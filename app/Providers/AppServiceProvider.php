<?php

namespace Statamic\Providers;

use Statamic\API\File;
use Statamic\DataStore;
use Statamic\Sites\Sites;
use Statamic\Statamic;
use Stringy\StaticStringy;
use Statamic\Routing\Router;
use Statamic\Exceptions\Handler;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Statamic\API\Nav; // TODO: Remove!

class AppServiceProvider extends ServiceProvider
{
    private $root = __DIR__.'/../..';

    public function boot()
    {
        Nav::extend(function ($nav) {
            // $this->messingWithNav();
        });

        $this->swapSessionMiddleware();

        $this->app[\Illuminate\Contracts\Http\Kernel::class]
             ->pushMiddleware(\Statamic\Http\Middleware\PermanentRedirects::class)
             ->pushMiddleware(\Statamic\Http\Middleware\VanityRedirects::class)
             ->pushMiddleware(\Statamic\Http\Middleware\PoweredByHeader::class);

        $this->app->booted(function () {
            $this->loadRoutesFrom("{$this->root}/routes/routes.php");
        });

        $this->loadViewsFrom("{$this->root}/resources/views", 'statamic');

        collect(['api', 'assets', 'cp', 'forms', 'protect', 'routes', 'search', 'static_caching', 'sites', 'stache', 'system', 'theming', 'users'])->each(function ($config) {
            $this->mergeConfigFrom("{$this->root}/config/$config.php", "statamic.$config");
            $this->publishes(["{$this->root}/config/$config.php" => config_path("statamic/$config.php")], 'statamic');
        });

        $this->publishes([
            "{$this->root}/config/user_roles.yaml" => config('statamic.users.roles.path', config_path('statamic/user_roles.yaml')),
            "{$this->root}/config/user_groups.yaml" => config('statamic.users.groups.path', config_path('statamic/user_groups.yaml'))
        ], 'statamic');

        $this->publishes([
            "{$this->root}/resources/dist" => public_path('resources/cp')
        ], 'statamic-cp');

        $this->loadTranslationsFrom("{$this->root}/resources/lang", 'statamic');
        $this->loadJsonTranslationsFrom("{$this->root}/resources/lang");

        $this->publishes([
            "{$this->root}/resources/lang" => resource_path('lang/vendor/statamic')
        ], 'statamic-translations');

        Blade::directive('svg', function ($expression) {
            $file = trim($expression, "'");
            return StaticStringy::collapseWhitespace(
                File::get(statamic_path("resources/dist/svg/{$file}.svg"))
            );
        });

        $this->app['redirect']->macro('cpRoute', function ($route, $parameters = []) {
            return $this->to(cp_route($route, $parameters));
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
            \Statamic\Contracts\Data\Repositories\TaxonomyRepository::class => \Statamic\Stache\Repositories\TaxonomyRepository::class,
            \Statamic\Contracts\Data\Repositories\CollectionRepository::class => \Statamic\Stache\Repositories\CollectionRepository::class,
            \Statamic\Contracts\Data\Repositories\GlobalRepository::class => \Statamic\Stache\Repositories\GlobalRepository::class,
            \Statamic\Contracts\Data\Repositories\AssetContainerRepository::class => \Statamic\Stache\Repositories\AssetContainerRepository::class,
            \Statamic\Contracts\Data\Repositories\ContentRepository::class => \Statamic\Stache\Repositories\ContentRepository::class,
            \Statamic\Contracts\Data\Repositories\StructureRepository::class => \Statamic\Stache\Repositories\StructureRepository::class,
        ])->each(function ($concrete, $abstract) {
            $this->app->singleton($abstract, $concrete);
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

    protected function messingWithNav()
    {
        // This nav item will go into the 'Content' section.
        Nav::content('Floof')->url('/floof')->icon('fa-floof');

        // This nav item will go into the 'Tools' section.
        Nav::tools('Floof Utilities')
            ->url('/floof-utilities')
            ->icon('fa-floof')
            ->children([
                'Feeder' => '/feeder',                 // Since children are simpler, can define name and URL like this
                Nav::item('Groomer')->url('/groomer'), // Or explicitly using Nav::item
            ]);

        // This nav item will go into a new custom 'Wordpress' section.
        Nav::wordpress('Importer')->url('/wordpress-importer')->icon('fa-import');

        // Removing items.
        Nav::remove('Content', 'Collections'); // Remove a single item.
        Nav::remove('Wordpress');              // Remove a whole section.
    }
}
