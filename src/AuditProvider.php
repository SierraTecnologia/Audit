<?php

namespace Audit;

use Audit\Http\Middleware\Audits;
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

    public $packageName = 'audit';
    const pathVendor = 'sierratecnologia/audit';

    // @todo Usar Tools aqui pro providers
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Audit\Http\Controllers';

    public static $aliasProviders = [
        
    ];

    public static $providers = [
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
     * Rotas do Menu
     */
    public static $menuItens = [
        [
            'text' => 'Auditoria',
            'icon' => 'fas fa-fw fa-chart-area',
            'icon_color' => "blue",
            'label_color' => "success",
            'order' => 2550,
            'section' => "admin",
            'level'       => 2, // 0 (Public), 1, 2 (Admin) , 3 (Root)
        ],
        'Auditoria' => [
            [
                'text'        => 'Logs',
                'route'       => 'admin.tracking.larametrics::metrics.index',
                'icon'        => 'dashboard',
                'icon_color'  => 'blue',
                'label_color' => 'success',
                'level'       => 2,
                'order' => 2550,
                'section' => "admin",
                // 'access' => \Porteiro\Models\Role::$ADMIN
            ],
            [
                'text'        => 'Telescope',
                'route'       => 'telescope',
                'icon'        => 'dashboard',
                'icon_color'  => 'blue',
                'label_color' => 'success',
                'level'       => 2,
                'order' => 2550,
                'section' => "admin",
                // 'access' => \Porteiro\Models\Role::$ADMIN
            ],
            [
                'text'        => 'Horizon',
                'route'       => 'horizon.index',
                'icon'        => 'dashboard',
                'icon_color'  => 'blue',
                'label_color' => 'success',
                'order' => 2550,
                'section' => "admin",
                'level'       => 2,
                // 'access' => \Porteiro\Models\Role::$ADMIN
            ],
        ],
    ];

    /**
     * Alias the services in the boot.
     */
    public function boot(Router $router)
    {
        // Push middleware to web group
        $router->pushMiddlewareToGroup('web', Audits::class);

        // Register configs, migrations, etc
        $this->registerDirectories();
        

        /**
         * Transmissor; Routes
         */
        $this->loadRoutesForRiCa(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'routes');


        // Log model change events after others in case they modified the record
        // before being saved.
        $this->app['events']->listen(
            'eloquent.*',
            'Audit\Observers\Changes'
        );
    }

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
        $this->loadMigrationsFrom(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations');

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
            $this->getPublishesPath('config'.DIRECTORY_SEPARATOR.'sitec') => config_path('sitec'),
            // Files
            $this->getPublishesPath('config/activitylog.php') => config_path('activitylog.php'),
            $this->getPublishesPath('config/logging.php') => config_path('logging.php'),
            $this->getPublishesPath('config/logviewer.php') => config_path('logviewer.php'),
            $this->getPublishesPath('config/telescope.php') => config_path('telescope.php')
            ],
            ['config',  'sitec', 'sitec-config']
        );

        // // Publish audit css and js to public directory
        // $this->publishes([
        //     $this->getDistPath() => public_path('assets/audit')
        // ], 'assets');



        // Publish audit css and js to public directory
        $this->publishes(
            [
            $this->getPublishesPath('public/telescope') => public_path('vendor/telescope'),
            ],
            ['public',  'sitec', 'sitec-public']
        );


        $this->loadViews();
        $this->loadTranslations();
    }

    private function loadViews()
    {
        // View namespace
        $viewsPath = $this->getResourcesPath('views');
        $this->loadViewsFrom($viewsPath, 'audit');
        $this->publishes(
            [
            $viewsPath => base_path('resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'audit'),
            $this->getPublishesPath('views/laravel-log-viewer') => base_path('resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'laravel-log-viewer'),
            ],
            ['views', 'sitec', 'sitec-views', 'ricasolucoes', 'ricasolucoes-views', 'audit-views']
        );


        // // Publish lanaguage files
        // $this->publishes([
        //     $this->getResourcesPath('lang') => resource_path('lang'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'audit')
        // ], 'lang');

        // // Load translations
        // $this->loadTranslationsFrom($this->getResourcesPath('lang'), 'audit');
    }
    
    private function loadTranslations()
    {
        // $translationsPath = $this->getResourcesPath('lang');
        // $this->loadTranslationsFrom($translationsPath, 'audit');
        // $this->publishes([
        //     $translationsPath => resource_path('lang'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'audit'),
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
}
