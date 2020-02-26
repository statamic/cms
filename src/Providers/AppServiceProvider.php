<?php

namespace Statamic\Providers;

use Statamic\Facades\File;
use Statamic\Statamic;
use Statamic\Sites\Sites;
use Stringy\StaticStringy;
use Statamic\Facades\Preference;
use Illuminate\Support\Carbon;
use Statamic\Exceptions\Handler;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;

class AppServiceProvider extends ServiceProvider
{
    protected $root = __DIR__.'/../..';

    protected $configFiles = [
        'amp', 'api', 'assets', 'cp', 'forms', 'live_preview', 'oauth', 'protect', 'revisions',
        'routes', 'search', 'static_caching', 'sites', 'stache', 'system', 'users'
    ];

    public function boot()
    {
        $this->swapSessionMiddleware();

        $this->app[\Illuminate\Contracts\Http\Kernel::class]
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
            "{$this->root}/resources/users" => resource_path('users'),
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

        $this->app->singleton(Sites::class, function () {
            return new Sites(config('statamic.sites'));
        });

        collect([
            \Statamic\Contracts\Entries\EntryRepository::class => \Statamic\Stache\Repositories\EntryRepository::class,
            \Statamic\Contracts\Taxonomies\TermRepository::class => \Statamic\Stache\Repositories\TermRepository::class,
            \Statamic\Contracts\Taxonomies\TaxonomyRepository::class => \Statamic\Stache\Repositories\TaxonomyRepository::class,
            \Statamic\Contracts\Entries\CollectionRepository::class => \Statamic\Stache\Repositories\CollectionRepository::class,
            \Statamic\Contracts\Globals\GlobalRepository::class => \Statamic\Stache\Repositories\GlobalRepository::class,
            \Statamic\Contracts\Assets\AssetContainerRepository::class => \Statamic\Stache\Repositories\AssetContainerRepository::class,
            \Statamic\Contracts\Data\Repositories\ContentRepository::class => \Statamic\Stache\Repositories\ContentRepository::class,
            \Statamic\Contracts\Structures\StructureRepository::class => \Statamic\Structures\StructureRepository::class,
            \Statamic\Contracts\Structures\NavigationRepository::class => \Statamic\Stache\Repositories\NavigationRepository::class,
            \Statamic\Contracts\Assets\AssetRepository::class => \Statamic\Assets\AssetRepository::class,
        ])->each(function ($concrete, $abstract) {
            $this->app->singleton($abstract, $concrete);
        });

        $this->app->singleton(\Statamic\Contracts\Data\DataRepository::class, function ($app) {
            return (new \Statamic\Data\DataRepository)
                ->setRepository('entry', \Statamic\Contracts\Entries\EntryRepository::class)
                ->setRepository('term', \Statamic\Contracts\Taxonomies\TermRepository::class)
                ->setRepository('taxonomy', \Statamic\Contracts\Taxonomies\TaxonomyRepository::class)
                ->setRepository('global', \Statamic\Contracts\Globals\GlobalRepository::class)
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
        $this->app->singleton(
            \Illuminate\Session\Middleware\StartSession::class,
            \Statamic\Http\Middleware\CP\StartSession::class
        );
    }
}
