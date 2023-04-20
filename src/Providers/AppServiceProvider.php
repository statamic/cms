<?php

namespace Statamic\Providers;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Statamic\Facades;
use Statamic\Facades\Addon;
use Statamic\Facades\Preference;
use Statamic\Facades\Token;
use Statamic\Sites\Sites;
use Statamic\Statamic;

class AppServiceProvider extends ServiceProvider
{
    protected $root = __DIR__.'/../..';

    protected $configFiles = [
        'amp', 'antlers', 'api', 'assets', 'autosave', 'cp', 'editions', 'forms', 'git', 'graphql', 'live_preview', 'oauth', 'protect', 'revisions',
        'routes', 'search', 'static_caching', 'sites', 'stache', 'system', 'users',
    ];

    public function boot()
    {
        $this->app->booted(function () {
            Statamic::runBootedCallbacks();
            $this->loadRoutesFrom("{$this->root}/routes/routes.php");
        });

        $this->registerMiddlewareGroup();

        $this->app[\Illuminate\Contracts\Http\Kernel::class]
            ->pushMiddleware(\Statamic\Http\Middleware\PoweredByHeader::class)
            ->pushMiddleware(\Statamic\Http\Middleware\CheckComposerJsonScripts::class)
            ->pushMiddleware(\Statamic\Http\Middleware\CheckMultisite::class)
            ->pushMiddleware(\Statamic\Http\Middleware\DisableFloc::class);

        $this->loadViewsFrom("{$this->root}/resources/views", 'statamic');

        collect($this->configFiles)->each(function ($config) {
            $this->publishes(["{$this->root}/config/$config.php" => config_path("statamic/$config.php")], 'statamic');
        });

        $this->publishes([
            "{$this->root}/resources/users" => resource_path('users'),
        ], 'statamic');

        $this->publishes([
            "{$this->root}/resources/dist" => public_path('vendor/statamic/cp'),
        ], 'statamic-cp');

        $this->publishes([
            "{$this->root}/resources/dist-frontend" => public_path('vendor/statamic/frontend'),
        ], 'statamic-frontend');

        $this->loadTranslationsFrom("{$this->root}/resources/lang", 'statamic');
        $this->loadJsonTranslationsFrom("{$this->root}/resources/lang");

        $this->publishes([
            "{$this->root}/resources/lang" => app()->langPath().'/vendor/statamic',
        ], 'statamic-translations');

        $this->loadViewsFrom("{$this->root}/resources/views/extend", 'statamic');

        $this->publishes([
            "{$this->root}/resources/views/extend/forms" => resource_path('views/vendor/statamic/forms'),
        ], 'statamic-forms');

        $this->app['redirect']->macro('cpRoute', function ($route, $parameters = []) {
            return $this->to(cp_route($route, $parameters));
        });

        Carbon::macro('inPreferredFormat', function () {
            return $this->format(
                Preference::get('date_format', config('statamic.cp.date_format'))
            );
        });

        Request::macro('statamicToken', function () {
            if ($token = $this->token ?? $this->header('X-Statamic-Token')) {
                return Token::find($token);
            }
        });

        $this->addAboutCommandInfo();
    }

    public function register()
    {
        collect($this->configFiles)->each(function ($config) {
            $this->mergeConfigFrom("{$this->root}/config/$config.php", "statamic.$config");
        });

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
            \Statamic\Contracts\Structures\StructureRepository::class => \Statamic\Structures\StructureRepository::class,
            \Statamic\Contracts\Structures\CollectionTreeRepository::class => \Statamic\Stache\Repositories\CollectionTreeRepository::class,
            \Statamic\Contracts\Structures\NavTreeRepository::class => \Statamic\Stache\Repositories\NavTreeRepository::class,
            \Statamic\Contracts\Structures\NavigationRepository::class => \Statamic\Stache\Repositories\NavigationRepository::class,
            \Statamic\Contracts\Assets\AssetRepository::class => \Statamic\Assets\AssetRepository::class,
            \Statamic\Contracts\Forms\FormRepository::class => \Statamic\Forms\FormRepository::class,
        ])->each(function ($concrete, $abstract) {
            if (! $this->app->bound($abstract)) {
                Statamic::repository($abstract, $concrete);
            }
        });

        $this->app->singleton(\Statamic\Contracts\Data\DataRepository::class, function ($app) {
            return (new \Statamic\Data\DataRepository)
                ->setRepository('entry', \Statamic\Contracts\Entries\EntryRepository::class)
                ->setRepository('term', \Statamic\Contracts\Taxonomies\TermRepository::class)
                ->setRepository('collection', \Statamic\Contracts\Entries\CollectionRepository::class)
                ->setRepository('taxonomy', \Statamic\Contracts\Taxonomies\TaxonomyRepository::class)
                ->setRepository('global', \Statamic\Contracts\Globals\GlobalRepository::class)
                ->setRepository('asset', \Statamic\Contracts\Assets\AssetRepository::class)
                ->setRepository('user', \Statamic\Contracts\Auth\UserRepository::class);
        });

        $this->app->bind(\Statamic\Fields\BlueprintRepository::class, function () {
            return (new \Statamic\Fields\BlueprintRepository)
                ->setDirectory(resource_path('blueprints'))
                ->setFallback('default', function () {
                    return \Statamic\Facades\Blueprint::makeFromFields([
                        'content' => ['type' => 'markdown', 'localizable' => true],
                    ]);
                });
        });

        $this->app->singleton(\Statamic\Fields\FieldsetRepository::class, function () {
            return (new \Statamic\Fields\FieldsetRepository)
                ->setDirectory(resource_path('fieldsets'));
        });

        collect([
            'entries' => fn () => Facades\Entry::query(),
            'terms' => fn () => Facades\Term::query(),
            'assets' => fn () => Facades\Asset::query(),
            'users' => fn () => Facades\User::query(),
        ])->each(function ($binding, $alias) {
            app()->bind('statamic.queries.'.$alias, $binding);
        });

        $this->app->bind('statamic.imaging.guzzle', function () {
            return new \GuzzleHttp\Client;
        });
    }

    protected function registerMiddlewareGroup()
    {
        $this->app->make(Router::class)->middlewareGroup('statamic.web', [
            \Statamic\Http\Middleware\StacheLock::class,
            \Statamic\Http\Middleware\HandleToken::class,
            \Statamic\Http\Middleware\Localize::class,
            \Statamic\Http\Middleware\AddViewPaths::class,
            \Statamic\Http\Middleware\AuthGuard::class,
            \Statamic\StaticCaching\Middleware\Cache::class,
        ]);
    }

    protected function addAboutCommandInfo()
    {
        if (! class_exists(AboutCommand::class)) {
            return;
        }

        $addons = Addon::all();

        AboutCommand::add('Statamic', [
            'Version' => fn () => Statamic::version().' '.(Statamic::pro() ? '<fg=yellow;options=bold>PRO</>' : 'Solo'),
            'Antlers' => config('statamic.antlers.version'),
            'Addons' => $addons->count(),
            'Stache Watcher' => config('statamic.stache.watcher') ? 'Enabled' : 'Disabled',
            'Static Caching' => config('statamic.static_caching.strategy') ?: 'Disabled',
        ]);

        foreach ($addons as $addon) {
            AboutCommand::add('Statamic Addons', $addon->package(), $addon->version());
        }
    }
}
