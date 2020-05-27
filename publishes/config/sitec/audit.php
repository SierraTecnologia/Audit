<?php

return [
    'dontLog' => [
        \Aschmelyun\Larametrics\Models\LarametricsLog::class,
        \Illuminate\Database\Eloquent\Relations\Pivot::class,
    ],
    'dontLogAlias' => [
        'Tracking\Models',
        'Analytics',
        'Spatie\Analytics',
        'Wnx\LaravelStats',
        'Aschmelyun\Larametrics\Models',
        'Laravel\Horizon',
        'Support\Models\\Ardent',
        'Support\Models\\Coder',
    ],

];
