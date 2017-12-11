<?php

namespace Statamic\Extend;

use Statamic\API\Helper;
use Illuminate\Support\Facades\Event;
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
