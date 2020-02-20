<?php

namespace Statamic\Providers;

use Closure;
use Statamic\Support\Str;
use Statamic\Statamic;
use Statamic\Facades\Addon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Statamic\Exceptions\NotBootedException;

abstract class AddonServiceProvider extends ServiceProvider
{
    protected $listen = [];
    protected $subscribe = [];
    protected $tags = [];
    protected $fieldtypes = [];
    protected $modifiers = [];
    protected $widgets = [];
    protected $policies = [];
    protected $commands = [];
    protected $stylesheets = [];
    protected $scripts = [];
    protected $externalScripts = [];
    protected $publishables = [];
    protected $routes = [];
    protected $middleware = [];

    public function boot()
    {
        $this->app->booted(function () {
            if (! $this->getAddon()) {
                return;
            }

            $this
                ->bootEvents()
                ->bootTags()
                ->bootFieldtypes()
                ->bootModifiers()
                ->bootWidgets()
                ->bootCommands()
                ->bootSchedule()
                ->bootPolicies()
                ->bootStylesheets()
                ->bootScripts()
                ->bootPublishables()
                ->bootRoutes()
                ->bootMiddleware();
        });
    }

    public function bootEvents()
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }

        foreach ($this->subscribe as $subscriber) {
            Event::subscribe($subscriber);
        }

        return $this;
    }

    protected function bootTags()
    {
        foreach ($this->tags as $class) {
            $class::register();
        }

        return $this;
    }

    protected function bootFieldtypes()
    {
        foreach ($this->fieldtypes as $class) {
            $class::register();
        }

        return $this;
    }

    protected function bootModifiers()
    {
        foreach ($this->modifiers as $class) {
            $class::register();
        }

        return $this;
    }

    protected function bootWidgets()
    {
        foreach ($this->widgets as $class) {
            $class::register();
        }

        return $this;
    }

    protected function bootPolicies()
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }

        return $this;
    }

    protected function bootCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }

        return $this;
    }

    protected function bootSchedule()
    {
        if ($this->app->runningInConsole()) {
            $this->app->booted(function () {
                $this->schedule($this->app->make(Schedule::class));
            });
        }

        return $this;
    }

    protected function bootStylesheets()
    {
        foreach ($this->stylesheets as $path) {
            $this->registerStylesheet($path);
        }

        return $this;
    }

    protected function bootScripts()
    {
        foreach ($this->scripts as $path) {
            $this->registerScript($path);
        }

        foreach ($this->externalScripts as $path) {
            $this->registerExternalScript($path);
        }

        return $this;
    }

    protected function bootPublishables()
    {
        $package = $this->getAddon()->id();

        $publishables = collect($this->publishables)
            ->mapWithKeys(function ($destination, $origin) use ($package) {
                return [$origin => public_path("vendor/{$package}/{$destination}")];
            });

        $this->publishes($publishables->all());

        return $this;
    }

    protected function bootRoutes()
    {
        if ($web = array_get($this->routes, 'web')) {
            $this->registerWebRoutes($web);
        }

        if ($cp = array_get($this->routes, 'cp')) {
            $this->registerCpRoutes($cp);
        }

        if ($actions = array_get($this->routes, 'actions')) {
            $this->registerActionRoutes($actions);
        }

        return $this;
    }

    /**
     * Register routes from the root of the site.
     *
     * @param string|Closure $routes   Either the path to a routes file, or a closure containing routes.
     * @return void
     */
    public function registerWebRoutes($routes)
    {
        Statamic::pushWebRoutes(function () use ($routes) {
            Route::namespace('\\'.$this->namespace())->group($routes);
        });
    }

    /**
     * Register routes scoped to the addon's section in the Control Panel.
     *
     * @param string|Closure $routes   Either the path to a routes file, or a closure containing routes.
     * @return void
     */
    public function registerCpRoutes($routes)
    {
        Statamic::pushCpRoutes(function () use ($routes) {
            Route::namespace('\\'.$this->namespace())->group($routes);
        });
    }

    /**
     * Register routes scoped to the addon's front-end actions.
     *
     * @param string|Closure $routes   Either the path to a routes file, or a closure containing routes.
     * @return void
     */
    public function registerActionRoutes($routes)
    {
        Statamic::pushActionRoutes(function () use ($routes) {
            Route::namespace('\\'.$this->namespace())
                ->prefix($this->getAddon()->slug())
                ->group($routes);
        });
    }

    /**
     * Register a route group.
     *
     * @param string|Closure $routes   Either the path to a routes file, or a closure containing routes.
     * @param array $attributes  Additional attributes to be applied to the route group.
     * @return void
     */
    protected function registerRouteGroup($routes, array $attributes = [])
    {
        if (is_string($routes)) {
            $routes = function () use ($routes) {
                require $routes;
            };
        }

        Statamic::routes(function () use ($attributes, $routes) {
            Route::group($this->routeGroupAttributes($attributes), $routes);
        });
    }

    /**
     * The attributes to be applied to the route group.
     *
     * @param array $overrides  Any additional attributes.
     * @return array
     */
    protected function routeGroupAttributes($overrides = [])
    {
        return array_merge($overrides, [
            'namespace' => $this->getAddon()->namespace()
        ]);
    }

    protected function bootMiddleware()
    {
        foreach (array_get($this->middleware, 'web', []) as $middleware) {
            Statamic::pushWebMiddleware($middleware);
        }

        foreach (array_get($this->middleware, 'cp', []) as $middleware) {
            Statamic::pushCpMiddleware($middleware);
        }
    }

    public function registerScript(string $path)
    {
        $name = $this->getAddon()->id();
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $this->publishes([
            $path => public_path("vendor/{$name}/js/{$filename}.js"),
        ]);

        Statamic::script($name, $filename);
    }

    public function registerExternalScript(string $url)
    {
        Statamic::externalScript($url);
    }

    public function registerStylesheet(string $path)
    {
        $name = $this->getAddon()->id();
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $this->publishes([
            $path => public_path("vendor/{$name}/css/{$filename}.css"),
        ]);

        Statamic::style($name, $filename);
    }

    protected function schedule($schedule)
    {
        //
    }

    protected function namespace()
    {
        return $this->getAddon()->namespace();
    }

    private function getAddon()
    {
        throw_unless($this->app->isBooted(), new NotBootedException);

        $class = get_class($this);

        return Addon::all()->first(function ($addon) use ($class) {
            return Str::startsWith($class, $addon->namespace());
        });
    }
}
