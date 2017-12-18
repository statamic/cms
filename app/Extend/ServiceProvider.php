<?php

namespace Statamic\Extend;

use Statamic\API\Helper;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

abstract class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Provides access to addon helper methods
     */
    use Extensible;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function registerEventListener($class)
    {
        $listener = $this->app->make($class);

        foreach ($listener->events as $event => $methods) {
            foreach (Helper::ensureArray($methods) as $method) {
                Event::listen($event, [$listener, $method]);
            }
        }
    }

    /**
     * Register routes from the root of the site.
     *
     * @param string $path   Path to the routes file.
     * @return void
     */
    public function registerRoutes($path)
    {
        Route::group($this->routeGroupAttributes(), function () use ($path) {
            require $path;
        });
    }

    /**
     * Register routes scoped to the addon's section in the Control Panel.
     *
     * @param string $path  Path to the routes file.
     * @return void
     */
    public function registerCpRoutes($path)
    {
        $attributes = $this->routeGroupAttributes([
            'prefix' => config('cp.route') . '/' . $this->getAddon()->slug(),
        ]);

        Route::group($attributes, function () use ($path) {
            require $path;
        });
    }

    /**
     * Register routes scoped to the addon's front-end actions.
     *
     * @param string $path  Path to the routes file.
     * @return void
     */
    public function registerActionRoutes($path)
    {
        $attributes = $this->routeGroupAttributes([
            'prefix' => event_route() . '/' . $this->getAddon()->slug()
        ]);

        Route::group($attributes, function () use ($path) {
            require $path;
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

    /**
     * Register a tags class.
     *
     * @param string $tag    The name of the tag. (eg. "foo" would handle {{ foo }}, {{ foo:bar }}, etc)
     * @param string $class  The name of the class.
     * @return void
     */
    public function registerTags(string $tag, string $class)
    {
        $this->app['statamic.tags'][$tag] = $class;
    }

    /**
     * Register a modifier class.
     *
     * @param string $modifier  The name of the modifier. (eg. "foo" would handle {{ x | foo }})
     * @param string $class     The name of the class.
     * @return void
     */
    public function registerModifier(string $modifier, string $class)
    {
        $this->app['statamic.modifiers'][$modifier] = $class;
    }

    /**
     * Register a fieldtype class.
     *
     * @param string $fieldtype  The name of the fieldtype. (eg. "foo" would handle `type: foo`)
     * @param string $class      The name of the class.
     * @return void
     */
    public function registerFieldtype(string $fieldtype, string $class)
    {
        $this->app['statamic.fieldtypes'][$fieldtype] = $class;
    }
}
