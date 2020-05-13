<?php

namespace Audit\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Audit\Models\Audits;

class AuditsService
{
    public function __construct(Audits $model)
    {
        $this->model = $model;
    }

    public function log($request)
    {
        $requestData = json_encode([
            'referer' => $request->server('HTTP_REFERER', null),
            'user_agent' => $request->server('HTTP_USER_AGENT', null),
            'host' => $request->server('HTTP_HOST', null),
            'remote_addr' => $request->server('REMOTE_ADDR', null),
            'uri' => $request->server('REQUEST_URI', null),
            'method' => $request->server('REQUEST_METHOD', null),
            'query' => $request->server('QUERY_STRING', null),
            'time' => $request->server('REQUEST_TIME', null),
        ]);

        $route = 'todo/Route';
        $business = 0;
        $user = 0;

        /**
         * @todo data faltando
         */
        $requestData = md5($requestData);
        if (Schema::hasTable('audits')) {
            $this->model->create([
                'route' => $route,
                'business' => $business,
                'user' => $user,
                'data' => $requestData,
            ]);
        }
    }
}
