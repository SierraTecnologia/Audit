<?php

namespace Audit\Services;

class MenuService
{

    public static function getAdminMenu()
    {
        $audit = [];
        $audit[] = [
            'text'        => 'Logs',
            'url'         => route('larametrics::metrics.index'),
            'icon'        => 'dashboard',
            'icon_color'  => 'blue',
            'label_color' => 'success',
            // 'access' => \App\Models\Role::$ADMIN
        ];
        $audit[] = [
            'text'        => 'Telescope',
            'url'         => route('telescope'),
            'icon'        => 'dashboard',
            'icon_color'  => 'blue',
            'label_color' => 'success',
            // 'access' => \App\Models\Role::$ADMIN
        ];
        $audit[] = [
            'text'        => 'Horizon',
            'url'         => route('horizon.index'),
            'icon'        => 'dashboard',
            'icon_color'  => 'blue',
            'label_color' => 'success',
            // 'access' => \App\Models\Role::$ADMIN
        ];


        return $audit;
    }
}
