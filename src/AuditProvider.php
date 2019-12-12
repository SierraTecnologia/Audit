<?php

namespace Audit;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;

class AuditProvider extends ServiceProvider
{
    public static $providers = [
        \Audit\Providers\AuditRouteProvider::class,

        \Population\PopulationProvider::class,

        /**
         * Externos
         */
        \Laravel\Telescope\TelescopeServiceProvider::class,
        \Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider::class,
    ];

    /**
     * Alias the services in the boot.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'../resources/views' => base_path('resources/views/vendor/audit'),
        ], 'SierraTecnologia Audit');
    }

    /**
     * Register the services.
     */
    public function register()
    {
        $this->setProviders();

        // View namespace
        $this->loadViewsFrom(__DIR__.'../resources/views', 'audit');

        $this->loadMigrationsFrom(__DIR__.'/Migrations');

        // // Configs
        // $this->app->config->set('Audit.modules.Audit', include(__DIR__.'/config.php'));

        /*
        |--------------------------------------------------------------------------
        | Register the Commands
        |--------------------------------------------------------------------------
        */

        $this->commands([]);
    }

    private function setProviders()
    {
        (new Collection(self::$providers))->map(function ($provider) {
            $this->app->register($provider);
        });
    }

}
