<?php

namespace Audit\Observers;

// Deps

use Audit\Models\Change;
use Event;
use Route;

/**
 * Create a log of all model changing events
 */
class Changes
{
    /**
     * Only log the following events
     *
     * @param array
     */
    protected $supported = ['created', 'updated', 'deleted'];

    protected $action = false;

    /**
     * Handle all Eloquent model events
     *
     * @param string $event
     * @param array  $payload Contains:
     *                        -
     *                        Audit\Models\Base
     *                        $model
     *
     * @return void
     */
    public function handle($event, $payload)
    {
        list($model) = $payload;
        if ($this->isToIgnore($model, $event)) {
            return;
        }


        // If `log_changes` was configed as a callable, see if this model event
        // should not be logged
        if ($check = \Illuminate\Support\Facades\Config::get('sitec.site.log_changes')) {
            if (is_bool($check) && !$check) {
                return;
            }
            // Get the admin acting on the record
            $admin =  $this->getUser();
            if (is_callable($check)) {
                if (!call_user_func($check, $model, $this->action, $admin)) {
                    return;
                }
            }
        } else {
            return;
        }

        // Check with the model itself to see if it should be logged
        if (method_exists($model, 'shouldLogChange')) {
            if (!$model->shouldLogChange($this->action)) {
                return;
            }
            // Default to not logging changes if there is no shouldLogChange()
        } else {
            return;
        }
        \Log::debug('[Audit] Log Changes: '.get_class($model));
        // \Log::debug('[Audit] Log Changes: '.print_r($event, true).print_r($payload, true));

        // Log the event
        Change::log($model, $this->action, $admin);
    }

    protected function getUser()
    {
        return app('facilitador.user');
    }

    protected function isToIgnore($model, string $event): bool
    {
        // Don't log changes to pivot models.  Even though a user may have initiated
        // this, it's kind of meaningless to them.  These events can happen when a
        // user messes with drag and drop positioning.
        if (!empty($this->getDontLog())) {
            foreach ($this->getDontLog() as $logClass) {
                if (is_a($model, $logClass)) {
                    return true;
                }
            }
        }
        if (!empty($this->getDontLogAlias())) {
            foreach ($this->getDontLogAlias() as $logClassAlias) {
                if (strpos($event, $logClassAlias) !== false) {
                    return true;
                }
            }
        }

        // Get the action of the event
        preg_match('#eloquent\.(\w+)#', $event, $matches);
        $this->action = $matches[1];
        if (!in_array($this->action, $this->supported)) {
            return true;
        }

        return false;
    }

    protected function getDontLog()
    {
        return \Illuminate\Support\Facades\Config::get(
            'sitec.audit.dontLog',
            [
                \Aschmelyun\Larametrics\Models\LarametricsLog::class,
                \Illuminate\Database\Eloquent\Relations\Pivot::class,
            ]
        );
    }

    protected function getDontLogAlias()
    {
        return \Illuminate\Support\Facades\Config::get(
            'sitec.audit.dontLogAlias',
            [
                'Tracking\Models',
                'Analytics',
                'Spatie\Analytics',
                'Wnx\LaravelStats',
                'Aschmelyun\Larametrics\Models',
                'Laravel\Horizon',
                'Support\Models\Application',
                'Support\Models\Ardent',
                'Support\Models\Code',
            ]
        );
    }
}
