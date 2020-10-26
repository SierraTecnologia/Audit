<?php

namespace Audit\Http\Controllers;

use Response;
use Audit\Models\Change;
use Illuminate\Http\Request;

/**
 * A log of model changes, used for auditing Admin activity. Can also be used
 * as a source for recovering changed / deleted content.
 */
class Changes extends Base
{
    /**
     * @var string
     */
    public $title = 'Changes';

    /**
     * @var string
     */
    public $description = 'A log of actions that can be used to audit <b>Admin</b> activity or recover content.';

    /**
     * @var array
     */
    public $columns = [
        'Activity' => 'getAdminTitleHtmlAttribute',
    ];

    /**
     * Make search options dependent on whether the site is using roles
     *
     * @return array
     */
    public function search()
    {
        $options = [
            'model' => [
                'label' => __('pedreiro::changes.controller.search.type'),
                'type' => 'text',
            ],
            'key' => [
                'label' => __('pedreiro::changes.controller.search.key'),
                'type' => 'text',
            ],
            'action' => [
                'label' => __('pedreiro::changes.controller.search.action'),
                'type' => 'select',
                'options' => 'Audit\Models\Change::getActions()',
            ],
            'title' => [
                'label' => __('pedreiro::changes.controller.search.title'),
                'type' => 'text',
            ],
            'changeable_id' => [
                'label' => __('pedreiro::changes.controller.search.admin'),
                'type' => 'select',
                'options' => 'Audit\Models\Change::getAdmins()',
            ],
            'created_at' => [
                'label' => __('pedreiro::changes.controller.search.date'),
                'type' => 'date',
            ],
        ];

        return $options;
    }

    /**
     * Only reading is possible
     *
     * @return array An associative array.
     */
    public function getPermissionOptions()
    {
        return [
            'read' => 'View changes of all content',
        ];
    }

    /**
     * Customize the edit view to return the changed attributes as JSON. Using
     * this method / action so that a new routing rule doesn't need to be created
     *
     * @param  int $id Model key
     * @return Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $change = Change::findOrFail($id);
        $admin = $change->admin;
        return Response::json(
            [
            'action' => __("facilitador::changes.actions.$change->action"),
            'title' => $change->title,
            'admin' => $admin ? $admin->getAdminTitleHtmlAttribute() : 'someone',
            'admin_edit' => $admin ? $admin->getAdminEditAttribute() : null,
            'date' => $change->getHumanDateAttribute(),
            'attributes' => $change->attributesForModal(),
            ]
        );
    }

    /**
     * Populate protected properties on init
     */
    public function __construct()
    {
        $this->title = __('pedreiro::changes.controller.title');
        $this->description = __('pedreiro::changes.controller.description');
        $this->columns = [
            __('pedreiro::changes.controller.column.activity') => 'getAdminTitleHtmlAttribute',
        ];

        parent::__construct();
    }
}
