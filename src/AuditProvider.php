<?php

namespace Audit;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;

class AuditProvider extends ServiceProvider
{
    public static $aliasProviders = [
        
    ];

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
        ], 'audit');
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

    /**
     * Load Alias and Providers
     */
    private function setProviders()
    {
        $this->setDependencesAlias();
        (new Collection(self::$providers))->map(function ($provider) {
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        });
    }
    private function setDependencesAlias()
    {
        $loader = AliasLoader::getInstance();
        (new Collection(self::$aliasProviders))->map(function ($class, $alias) use ($loader) {
            $loader->alias($alias, $class);
        });
    }

}
