<?php

namespace Audit;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Schema;
use Route;

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
        // Register configs, migrations, etc
        $this->registerDirectories();
        

        // // Wire up model event callbacks even if request is not for admin.  Do this
        // // after the usingAdmin call so that the callbacks run after models are
        // // mutated by Decoy logic.  This is important, in particular, so the
        // // Validation observer can alter validation rules before the onValidation
        // // callback runs.
        // $this->app['events']->listen('eloquent.*',
        //     'Audit\Observers\ModelCallbacks');
        // $this->app['events']->listen('audit::model.*',
        //     'Audit\Observers\ModelCallbacks');
        // // Log model change events after others in case they modified the record
        // // before being saved.
        // $this->app['events']->listen('eloquent.*',
        //     'Audit\Observers\Changes');
    }

    /**
     * Register the services.
     */
    public function register()
    {
        // Merge own configs into user configs 
        $this->mergeConfigFrom($this->getPublishesPath('config/activitylog.php'), 'activitylog');
        $this->mergeConfigFrom($this->getPublishesPath('config/logging.php'), 'logging');
        $this->mergeConfigFrom($this->getPublishesPath('config/logviewer.php'), 'logviewer');
        $this->mergeConfigFrom($this->getPublishesPath('config/telescope.php'), 'telescope');

        // Register external packages
        $this->setProviders();
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
     * Delegate events to Decoy observers
     *
     * @return void
     */
    protected function delegateAdminObservers()
    {
        $this->app['events']->listen('eloquent.saving:*',
            '\Audit\Observers\Localize');
        $this->app['events']->listen('eloquent.saving:*',
            '\Audit\Observers\Encoding@onSaving');
        $this->app['events']->listen('eloquent.saved:*',
            '\Audit\Observers\ManyToManyChecklist');
        $this->app['events']->listen('eloquent.deleted:*',
            '\Audit\Observers\Encoding@onDeleted');
        $this->app['events']->listen('audit::model.validating:*',
            '\Audit\Observers\ValidateExistingFiles@onValidating');
    }

    /**
     * Register middlewares
     *
     * @return void
     */
    protected function registerMiddlewares()
    {

        // Register middleware individually
        foreach ([
            'audit.auth'          => \Audit\Http\Middleware\Auth::class,
            'audit.edit-redirect' => \Audit\Http\Middleware\EditRedirect::class,
            'audit.guest'         => \Audit\Http\Middleware\Guest::class,
            'audit.save-redirect' => \Audit\Http\Middleware\SaveRedirect::class,
        ] as $key => $class) {
            $this->app['router']->aliasMiddleware($key, $class);
        }

        // This group is used by public audit routes
        $this->app['router']->middlewareGroup('audit.public', [
            'web',
        ]);

        // The is the starndard auth protected group
        $this->app['router']->middlewareGroup('audit.protected', [
            'web',
            'audit.auth',
            'audit.save-redirect',
            'audit.edit-redirect',
        ]);

        // Require a logged in admin session but no CSRF token
        $this->app['router']->middlewareGroup('audit.protected_endpoint', [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            'audit.auth',
        ]);

        // An open endpoint, like used by Zendcoder
        $this->app['router']->middlewareGroup('audit.endpoint', [
            'api'
        ]);
    }
    /**
     * Register configs, migrations, etc
     *
     * @return void
     */
    public function registerDirectories()
    {
        // Publish config files
        $this->publishes([
            // Paths
            $this->getPublishesPath('config/audit') => config_path('audit'),
            // Files
            $this->getPublishesPath('config/activitylog.php') => config_path('activitylog.php'),
            $this->getPublishesPath('config/logging.php') => config_path('logging.php'),
            $this->getPublishesPath('config/logviewer.php') => config_path('logviewer.php'),
            $this->getPublishesPath('config/telescope.php') => config_path('telescope.php')
        ], ['config', 'sitec-config']);

        // // Publish audit css and js to public directory
        // $this->publishes([
        //     $this->getDistPath() => public_path('assets/audit')
        // ], 'assets');



        // Publish audit css and js to public directory
        $this->publishes([
            $this->getPublishesPath('public/horizon') => public_path('vendor/horizon'),
            $this->getPublishesPath('public/larametrics') => public_path('vendor/larametrics')
        ], ['public', 'sitec-public']);


        $this->loadViews();
        $this->loadTranslations();

    }

    private function loadViews()
    {
        // View namespace
        $viewsPath = $this->getResourcesPath('views');
        $this->loadViewsFrom($viewsPath, 'audit');
        $this->publishes([
            $viewsPath => base_path('resources/views/vendor/audit'),
            $this->getPublishesPath('views/laravel-log-viewer') => base_path('resources/views/vendor/laravel-log-viewer'),
        ], ['views', 'sitec-views']);


        // // Publish lanaguage files
        // $this->publishes([
        //     $this->getResourcesPath('lang') => resource_path('lang/vendor/audit')
        // ], 'lang');

        // // Load translations
        // $this->loadTranslationsFrom($this->getResourcesPath('lang'), 'audit');
    }
    
    private function loadTranslations()
    {
        // $translationsPath = $this->getResourcesPath('lang');
        // $this->loadTranslationsFrom($translationsPath, 'audit');
        // $this->publishes([
        //     $translationsPath => resource_path('lang/vendor/audit'),
        // ], 'translations');// @todo ou lang, verificar (invez de translations)
    }

    /**
     * Configs Paths
     */
    private function getResourcesPath($folder)
    {
        return __DIR__.'/../resources/'.$folder;
    }

    private function getPublishesPath($folder)
    {
        return __DIR__.'/../publishes/'.$folder;
    }

    private function getDistPath($folder = '')
    {
        return __DIR__.'/../dist/'.$folder;
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
