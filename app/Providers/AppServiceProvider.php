<?php

namespace Statamic\Providers;

use Statamic\API\File;
use Statamic\Statamic;
use Statamic\DataStore;
use Statamic\Sites\Sites;
use Stringy\StaticStringy;
use Statamic\API\Preference;
use Statamic\Routing\Router;
use Illuminate\Support\Carbon;
use Statamic\Exceptions\Handler;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Statamic\Ignition\SolutionProviders;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Facade\IgnitionContracts\SolutionProviderRepository;

class AppServiceProvider extends ServiceProvider
{
    protected $root = __DIR__.'/../..';

    protected $configFiles = [
        'amp', 'api', 'assets', 'cp', 'forms', 'live_preview', 'oauth', 'protect', 'revisions',
        'routes', 'search', 'static_caching', 'sites', 'stache', 'system', 'theming', 'users'
    ];

    public function boot()
    {
        $this->registerIgnitionSolutionProviders();

        $this->swapSessionMiddleware();

        $this->app[\Illuminate\Contracts\Http\Kernel::class]
             ->pushMiddleware(\Statamic\Http\Middleware\PermanentRedirects::class)
             ->pushMiddleware(\Statamic\Http\Middleware\VanityRedirects::class)
             ->pushMiddleware(\Statamic\Http\Middleware\PoweredByHeader::class);

        $this->app->booted(function () {
            $this->loadRoutesFrom("{$this->root}/routes/routes.php");
        });

        $this->loadViewsFrom("{$this->root}/resources/views", 'statamic');

        collect($this->configFiles)->each(function ($config) {
            $this->mergeConfigFrom("{$this->root}/config/$config.php", "statamic.$config");
            $this->publishes(["{$this->root}/config/$config.php" => config_path("statamic/$config.php")], 'statamic');
        });

        $this->publishes([
            "{$this->root}/config/user_roles.yaml" => config_path('statamic/user_roles.yaml'),
            "{$this->root}/config/user_groups.yaml" => config_path('statamic/user_groups.yaml')
        ], 'statamic');

        $this->publishes([
            "{$this->root}/resources/dist" => public_path('vendor/statamic/cp')
        ], 'statamic-cp');

        $this->loadTranslationsFrom("{$this->root}/resources/lang", 'statamic');
        $this->loadJsonTranslationsFrom("{$this->root}/resources/lang");

        $this->publishes([
            "{$this->root}/resources/lang" => resource_path('lang/vendor/statamic')
        ], 'statamic-translations');

        $this->loadViewsFrom("{$this->root}/resources/views/extend", 'statamic');

        $this->publishes([
            "{$this->root}/resources/views/extend" => resource_path('views/vendor/statamic')
        ], 'statamic-views');

        Blade::directive('svg', function ($expression) {
            return "<?php echo Statamic::svg({$expression}) ?>";
        });

        $this->app['redirect']->macro('cpRoute', function ($route, $parameters = []) {
            return $this->to(cp_route($route, $parameters));
        });

        Carbon::setToStringFormat(config('statamic.system.date_format'));

        Carbon::macro('inPreferredFormat', function () {
            return $this->format(
                Preference::get('date_format', config('statamic.cp.date_format'))
            );
        });
    }

    public function register()
    {
        $this->app->singleton(ExceptionHandler::class, Handler::class);

        $this->app->singleton('Statamic\DataStore', function() {
            return new DataStore;
        });

        $this->app->bind(Router::class, function () {
            return new Router(config('statamic.routes.routes', []));
        });

        $this->app->singleton(Sites::class, function () {
            return new Sites(config('statamic.sites'));
        });

        collect([
            \Statamic\Contracts\Data\Repositories\EntryRepository::class => \Statamic\Stache\Repositories\EntryRepository::class,
            \Statamic\Contracts\Data\Repositories\TermRepository::class => \Statamic\Stache\Repositories\TermRepository::class,
            \Statamic\Contracts\Data\Repositories\TaxonomyRepository::class => \Statamic\Stache\Repositories\TaxonomyRepository::class,
            \Statamic\Contracts\Data\Repositories\CollectionRepository::class => \Statamic\Stache\Repositories\CollectionRepository::class,
            \Statamic\Contracts\Data\Repositories\GlobalRepository::class => \Statamic\Stache\Repositories\GlobalRepository::class,
            \Statamic\Contracts\Data\Repositories\AssetContainerRepository::class => \Statamic\Stache\Repositories\AssetContainerRepository::class,
            \Statamic\Contracts\Data\Repositories\ContentRepository::class => \Statamic\Stache\Repositories\ContentRepository::class,
            \Statamic\Contracts\Data\Repositories\StructureRepository::class => \Statamic\Stache\Repositories\StructureRepository::class,
            \Statamic\Contracts\Assets\AssetRepository::class => \Statamic\Assets\AssetRepository::class,
        ])->each(function ($concrete, $abstract) {
            $this->app->singleton($abstract, $concrete);
        });

        $this->app->singleton(\Statamic\Contracts\Data\Repositories\DataRepository::class, function ($app) {
            return (new \Statamic\Data\DataRepository)
                ->setRepository('route', \Statamic\Routing\RouteRepository::class)
                ->setRepository('entry', \Statamic\Contracts\Data\Repositories\EntryRepository::class)
                ->setRepository('term', \Statamic\Contracts\Data\Repositories\TermRepository::class)
                ->setRepository('taxonomy', \Statamic\Contracts\Data\Repositories\TaxonomyRepository::class)
                ->setRepository('global', \Statamic\Contracts\Data\Repositories\GlobalRepository::class)
                ->setRepository('asset', \Statamic\Contracts\Assets\AssetRepository::class)
                ->setRepository('user', \Statamic\Contracts\Auth\UserRepository::class);
        });

        $this->app->bind(\Statamic\Fields\BlueprintRepository::class, function ($app) {
            return (new \Statamic\Fields\BlueprintRepository($app['files']))
                ->setDirectory(resource_path('blueprints'))
                ->setFallbackDirectory(__DIR__.'/../../resources/blueprints');
        });

        $this->app->bind(\Statamic\Fields\FieldsetRepository::class, function ($app) {
            return (new \Statamic\Fields\FieldsetRepository($app['files']))
                ->setDirectory(resource_path('fieldsets'));
        });
    }

    protected function swapSessionMiddleware()
    {
        $middleware = version_compare($this->app->version(), '5.8.0', '<')
            ? \Statamic\Http\Middleware\CP\StartSession57::class
            : \Statamic\Http\Middleware\CP\StartSession::class;

        $this->app->singleton(\Illuminate\Session\Middleware\StartSession::class, $middleware);
    }

    protected function registerIgnitionSolutionProviders()
    {
        $this->app->make(SolutionProviderRepository::class)->registerSolutionProvider(
            SolutionProviders\OAuthDisabled::class
        );
    }
}
