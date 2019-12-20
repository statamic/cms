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

abstract class AddonServiceProvider extends ServiceProvider
{
    protected $listen = [];
    protected $subscribe = [];
    protected $tags = [];
    protected $fieldtypes = [];
    protected $modifiers = [];
    protected $commands = [];
    protected $stylesheets = [];
    protected $scripts = [];
    protected $externalScripts = [];
    protected $publishables = [];
    protected $routes = [];
    protected $middleware = [];

    public function boot()
    {
        if (! $this->addonDiscovered()) {
            return;
        }

        $this
            ->bootEvents()
            ->bootTags()
            ->bootFieldtypes()
            ->bootModifiers()
            ->bootCommands()
            ->bootSchedule()
            ->bootStylesheets()
            ->bootScripts()
            ->bootPublishables()
            ->bootRoutes()
            ->bootMiddleware();
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
        if (! $this->addonDiscovered()) {
            return;
        }

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
        if (! $this->addonDiscovered()) {
            return;
        }

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
        if (! $this->addonDiscovered()) {
            return;
        }

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
        if (! $this->addonDiscovered()) {
            return;
        }

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
        if (! $this->addonDiscovered()) {
            return;
        }

        foreach (array_get($this->middleware, 'web', []) as $middleware) {
            Statamic::pushWebMiddleware($middleware);
        }

        foreach (array_get($this->middleware, 'cp', []) as $middleware) {
            Statamic::pushCpMiddleware($middleware);
        }
    }

    public function registerScript(string $path)
    {
        if (! $this->addonDiscovered()) {
            return;
        }

        $name = $this->getAddon()->id();
        $filename = pathinfo($path, PATHINFO_FILENAME);

        $this->publishes([
            $path => public_path("vendor/{$name}/js/{$filename}.js"),
        ]);

        Statamic::script($name, $filename);
    }

    public function registerExternalScript(string $url)
    {
        if (! $this->addonDiscovered()) {
            return;
        }

        Statamic::externalScript($url);
    }

    public function registerStylesheet(string $path)
    {
        if (! $this->addonDiscovered()) {
            return;
        }

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
        $class = get_class($this);

        return Addon::all()->first(function ($addon) use ($class) {
            return Str::startsWith($class, $addon->namespace());
        });
    }

    private function addonDiscovered()
    {
        return $this->getAddon() !== null;
    }
}
