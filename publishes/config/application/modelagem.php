<?php

return [
    /**
     * Dont Audit Models
     */
    'dontLog' => [
        \Aschmelyun\Larametrics\Models\LarametricsLog::class,
        \Illuminate\Database\Eloquent\Relations\Pivot::class,
    ],
    'dontLogAlias' => [
        'Tracking\Models',
        'Analytics',
        'Activitys',
        'Spatie\Analytics',
        'Spatie\Activitylog\Models',
        'Wnx\LaravelStats',
        'Aschmelyun\Larametrics\Models',
        'Laravel\Horizon',
        'Support\Models\Application',
        'Support\Models\Ardent',
        'Support\Models\Code',
    ],

];
