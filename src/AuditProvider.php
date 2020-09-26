<?php

namespace Audit;

use Audit\Http\Middleware\Audits;
use Audit\Http\Middleware\isAjax;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Muleta\Traits\Providers\ConsoleTools;
use Route;

class AuditProvider extends ServiceProvider
{
    use ConsoleTools;

    public string $packageName = 'audit';
    const pathVendor = 'sierratecnologia/audit';

    // @todo Usar Tools aqui pro providers


    /**
     * @var array
     */
    public static array $aliasProviders = [
        
    ];

    /**
     * @var Providers\TelescopeServiceProvider::class|\Laravel\Telescope\TelescopeServiceProvider::class|\Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider::class[]
     *
     * @psalm-var array{0: Providers\TelescopeServiceProvider::class, 1: \Laravel\Telescope\TelescopeServiceProvider::class, 2: \Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider::class}
     */
    public static array $providers = [
        /**
         * Configuracoes
         */
        \Audit\Providers\TelescopeServiceProvider::class,
        
        /**
         * Externos
         */
        \Laravel\Telescope\TelescopeServiceProvider::class,
        \Rap2hpoutre\LaravelLogViewer\LaravelLogViewerServiceProvider::class,
    ];

    /**
     * Register the services.
     */
    public function register()
    {
        // Merge own configs into user configs
        $this->mergeConfigFrom($this->getPublishesPath('config/application/modelagem.php'), 'sitec.audit');

        $this->mergeConfigFrom($this->getPublishesPath('config/activitylog.php'), 'activitylog');
        $this->mergeConfigFrom($this->getPublishesPath('config/logging.php'), 'logging');
        $this->mergeConfigFrom($this->getPublishesPath('config/logviewer.php'), 'logviewer');
        $this->mergeConfigFrom($this->getPublishesPath('config/telescope.php'), 'telescope');

        // Register external packages
        $this->setProviders();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

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
     * Register configs, migrations, etc
     *
     * @return void
     */
    public function registerDirectories()
    {
        // Publish config files
        $this->publishes(
            [
            // Paths
            $this->getPublishesPath('config/sitec') => config_path('sitec'),
            // Files
            $this->getPublishesPath('config/activitylog.php') => config_path('activitylog.php'),
            $this->getPublishesPath('config/logging.php') => config_path('logging.php'),
            $this->getPublishesPath('config/logviewer.php') => config_path('logviewer.php'),
            $this->getPublishesPath('config/telescope.php') => config_path('telescope.php')
            ], ['config',  'sitec', 'sitec-config']
        );

        // // Publish audit css and js to public directory
        // $this->publishes([
        //     $this->getDistPath() => public_path('assets/audit')
        // ], 'assets');



        // Publish audit css and js to public directory
        $this->publishes(
            [
            $this->getPublishesPath('public/telescope') => public_path('vendor/telescope'),
            ], ['public',  'sitec', 'sitec-public']
        );


        $this->loadViews();
        $this->loadTranslations();
    }

    private function loadViews(): void
    {
        // View namespace
        $viewsPath = $this->getResourcesPath('views');
        $this->loadViewsFrom($viewsPath, 'audit');
        $this->publishes(
            [
            $viewsPath => base_path('resources/views/vendor/audit'),
            $this->getPublishesPath('views/laravel-log-viewer') => base_path('resources/views/vendor/laravel-log-viewer'),
            ], ['views',  'sitec', 'sitec-views', 'audit-views']
        );


        // // Publish lanaguage files
        // $this->publishes([
        //     $this->getResourcesPath('lang') => resource_path('lang/vendor/audit')
        // ], 'lang');

        // // Load translations
        // $this->loadTranslationsFrom($this->getResourcesPath('lang'), 'audit');
    }
    
    private function loadTranslations(): void
    {
        // $translationsPath = $this->getResourcesPath('lang');
        // $this->loadTranslationsFrom($translationsPath, 'audit');
        // $this->publishes([
        //     $translationsPath => resource_path('lang/vendor/audit'),
        // ], 'translations');// @todo ou lang, verificar (invez de translations)
    }

    /**
     * Configs Paths
     *
     * @return string
     */
    private function getResourcesPath(string $folder): string
    {
        return __DIR__.'/../resources/'.$folder;
    }

    private function getPublishesPath(string $folder): string
    {
        return __DIR__.'/../publishes/'.$folder;
    }

    /**
     * Load Alias and Providers
     *
     * @return void
     */
    private function setProviders(): void
    {
        $this->setDependencesAlias();
        (new Collection(self::$providers))->map(
            function ($provider) {
                if (class_exists($provider)) {
                    $this->app->register($provider);
                }
            }
        );
    }
    private function setDependencesAlias(): void
    {
        $loader = AliasLoader::getInstance();
        (new Collection(self::$aliasProviders))->map(
            function ($class, $alias) use ($loader) {
                $loader->alias($alias, $class);
            }
        );
    }
}
