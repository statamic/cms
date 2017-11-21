<?php

namespace Statamic\Extend;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

abstract class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Provides access to addon helper methods
     */
    use Extensible;

    /**
     * An array of additional service providers to be registered
     *
     * @var array
     */
    public $providers = [];

    /**
     * An array of additional aliases to be registered
     *
     * @var array
     */
    public $aliases = [];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register additional service providers
     *
     * @return void
     */
    public function registerAdditionalProviders()
    {
        foreach ($this->providers as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * Register additional aliases
     *
     * @return void
     */
    public function registerAdditionalAliases()
    {
        $loader = AliasLoader::getInstance();

        foreach ($this->aliases as $alias => $class) {
            $loader->alias($alias, $class);
        }
    }
}
