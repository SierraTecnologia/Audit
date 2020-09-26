<?php

namespace Audit\Models;

use DB;
use Facilitador;
use Config;
use SupportURL;
use Illuminate\Support\Str;
use Pedreiro\Template\Input\Search;
use Bkwld\Library\Utils\Text;
use Illuminate\Database\Eloquent\Model;
use Audit\Models\Base;
use App\Models\User;

/**
 * Reperesents a single model change event.  Typically a single CRUD action on
 * a model.
 */
class Change extends Base
{
    /**
     * The query param key used when previewing
     *
     * @var string
     */
    const QUERY_KEY = 'view-change';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'changed' => 'array',
    ];

    /**
     * @var string[]
     *
     * @psalm-var array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string, 6: string, 7: string, 8: string, 9: string, 10: string}
     */
    protected array $fillable = [
        'admin',
        'key',
        'changeable_id',
        'changed',
        'deleted',
        
        'action',
        'model',
        'model_title',
        'date',
        'title',
        'meta',
    ];

    /**
     * The polymorphic relation back to the parent model
     *
     * @var mixed
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function loggable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo('loggable', 'model', 'key');
    }

    /**
     * A convenience method for saving a change instance
     *
     * @param  Model  $model  The model being touched
     * @param  string $action Generally a CRUD verb: "created", "updated", "deleted"
     * @param  User   $admin  The admin acting on the record
     * @return static|void
     */
    public static function log(Model $model, $action, User $admin = null)
    {
        // Create a new change instance
        if (static::shouldWriteChange($model, $action)) {
            $changed = static::getChanged($model, $action);
            $change = static::createLog($model, $action, $admin, $changed);
        }

        // Log published / unpblished changes
        static::logPublishingChange($model, $action, $admin);

        // If the action was a deletion, mark all of the records for this model as
        // deleted
        if ($action == 'deleted') {
            DB::table('changes')
                ->where('model', get_class($model))
                ->where('key', $model->getKey())
                ->update(['deleted' => 1]);
        }

        // Return the changed instance
        if (isset($change)) {
            return $change;
        }
    }

    /**
     * Don't log changes when the only thing that changed was the published
     * state or updated timestamp.  We check if there are any attributes
     * besides these that changed.
     *
     * @param  Model  $model  The model being touched
     * @param  string $action
     * @return boolean
     */
    static private function shouldWriteChange(Model $model, $action)
    {
        if (in_array($action, ['created', 'deleted'])) { return true;
        }
        $changed_attributes = array_keys($model->getDirty());
        $ignored = ['updated_at', 'public'];
        $loggable = array_diff($changed_attributes, $ignored);
        return count($loggable) > 0;
    }

    /**
     * Get the changes attributes
     *
     * @param  Model  $model  The model being touched
     * @param  string $action
     * @return array|null
     */
    static private function getChanged(Model $model, $action): ?array
    {
        $changed = $model->getDirty();
        if ($action == 'deleted' || empty($changed)) {
            $changed = null;
        }
        return $changed;
    }

    /**
     * Create a change entry
     *
     * @param Model  $model  Th
     * @param string $action
     * @param User   $admin
     * @param array|null $changed
     *
     * @return self
     */
    static protected function createLog(
        Model $model,
        $action,
        User $admin = null,
        ?array $changed = null
    ): self {
        return static::create(
            [
            'model' => get_class($model),
            'key' => $model->getKey(),
            'action' => $action,
            'title' => static::getModelTitle($model),
            'changed' => $changed,
            'changeable_id' => static::getAdminId($admin),
            ]
        );
    }

    /**
     * Get the title of the model
     *
     * @param  Model $model
     * @return string
     */
    static protected function getModelTitle(Model $model)
    {
        return method_exists($model, 'getAdminTitleAttribute') ?
            $model->getAdminTitleAttribute() : null;
    }

    /**
     * Get the admin id
     *
     * @param  User $admin
     * @return integer
     */
    static protected function getAdminId(User $admin = null)
    {
        if (!$admin) {
            $admin = app('facilitador.user');
        }
        return $admin ? $admin->getKey() : null;
    }

    /**
     * Log changes to publishing state.  The initial publish should be logged
     * but not an initil unpublished state.
     *
     * @param  Model  $model
     * @param  string $action
     * @param  User   $admin
     * @return void
     */
    static public function logPublishingChange(
        Model $model,
        $action,
        User $admin = null
    ) {
        if ($model->isDirty('public')) {
            if ($model->public) {
                static::createLog($model, 'published', $admin);
            } else if (!$model->public && $action != 'created') {
                static::createLog($model, 'unpublished', $admin);
            }
        }
    }

    /**
     * Format the the activity like a sentence
     *
     * @return array|null|string HTML
     */
    public function getAdminTitleHtmlAttribute()
    {
        return __(
            'facilitador::changes.admin_title', [
            'admin' => $this->getAdminLinkAttribute(),
            'action' => $this->getActionLabelAttribute(),
            'model' => $this->getModelNameHtmlAttribute(),
            'model_title' => $this->getLinkedTitleAttribute(),
            'date' => $this->getDateAttribute()
            ]
        );
    }

    /**
     * Get the admin name and link
     *
     * @return string HTML
     */
    public function getAdminLinkAttribute()
    {
        if ($this->changeable_id) {
            return sprintf(
                '<a href="%s">%s</a>',
                $this->filterUrl(['changeable_id' => $this->changeable_id]),
                $this->admin->getAdminTitleHtmlAttribute()
            );
        } else {
            return 'Someone';
        }
    }

    /**
     * Format the activity as a colored label
     *
     * @return string HTML
     */
    public function getActionLabelAttribute()
    {
        $map = [
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'published' => 'info',
            'unpublished' => 'default',
        ];

        return sprintf(
            '<a href="%s" class="label label-%s">%s</a>',
            $this->filterUrl(['action' => $this->action]),
            isset($map[$this->action]) ? $map[$this->action] : 'info',
            __("facilitador::changes.actions.$this->action")
        );
    }

    /**
     * Format the model name by translating it through the controllers's defined
     * title
     *
     * @return string HTML
     */
    public function getModelNameHtmlAttribute()
    {
        $class = Facilitador::controllerForModel($this->model);

        // There is not a controller for the model
        if (!$class || !class_exists($class)) {
            return sprintf(
                '<b><a href="%s">%s</a></b>',
                $this->filterUrl(['model' => $this->model]),
                preg_replace('#(?<!\ )[A-Z]#', ' $0', $this->model)
            );
        }

        // There is a corresponding controller class
        $controller = new $class;
        return sprintf(
            '<b class="js-tooltip" title="%s"><a href="%s">%s</a></b>',
            htmlentities($controller->description()),
            $this->filterUrl(['model' => $this->model]),
            Str::singular($controller->title())
        );
    }

    /**
     * Get the title of the model. Perhaps in the future there will be more smarts
     * here, like generating a link to the edit view
     *
     * @return null|string HTML
     */
    public function getLinkedTitleAttribute()
    {
        if (!$this->title) { return;
        }
        return sprintf(
            '<a href="%s">"%s"</a>',
            $this->filterUrl(['model' => $this->model, 'key' => $this->key]),
            $this->title
        );
    }

    /**
     * Get the date of the change
     *
     * @return string HTML
     */
    public function getDateAttribute()
    {
        \Carbon\Carbon::setLocale(Facilitador::locale());
        if (is_null($this->created_at)) {
            return '-';
        }

        return sprintf(
            '<a href="%s" class="js-tooltip" title="%s">%s</a>',
            $this->filterUrl(['created_at' => $this->created_at->format('m/d/Y')]),
            $this->getHumanDateAttribute(),
            $this->created_at->diffForHumans()
        );
    }

    /**
     * Get the human readable date
     *
     * @return string
     */
    public function getHumanDateAttribute()
    {
        $format = __('facilitador::changes.human_date');
        if (is_null($format)) {
            $format = 'd \d\e F \d\e Y \à\s h\hi';
        }
        
        if (is_null($this->created_at)) {
            return '-';
        }

        return $this->created_at->format($format);
    }

    /**
     * Make a link to filter the result set
     *
     * @return string
     *
     * @param (Model|mixed)[] $query
     */
    public function filterUrl(array $query)
    {
        return SupportURL::action('changes').'?'.Search::query($query);
    }
}
