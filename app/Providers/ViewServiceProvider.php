<?php

namespace Statamic\Providers;

use Statamic\View\Store;
use Statamic\View\Modify;
use Statamic\View\Cascade;
use Statamic\View\Antlers\Parser;
use Statamic\Extensions\View\Factory;
use Illuminate\View\Engines\EngineResolver;
use Statamic\Extensions\View\FileViewFinder;
use Statamic\View\Antlers\Engine as AntlersEngine;
use Illuminate\View\ViewServiceProvider as LaravelViewServiceProvider;

class ViewServiceProvider extends LaravelViewServiceProvider
{
    protected $engines = ['file', 'php', 'blade', 'antlers'];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->singleton(Store::class);

        $this->app->singleton(Cascade::class, function ($app) {
            return new Cascade($app['request'], \Statamic\API\Site::current());
        });

        $this->app->bind(Parser::class, function ($app) {
            return (new Parser)->callback([AntlersEngine::class, 'renderTag']);
        });
    }

    /**
     * Create a new Factory Instance.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @param  \Illuminate\View\ViewFinderInterface  $finder
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return \Illuminate\View\Factory
     */
    protected function createFactory($resolver, $finder, $events)
    {
        return new Factory($resolver, $finder, $events);
    }

    /**
     * Register the engine resolver instance.
     *
     * This is the same as the parent method, but we've added `html` to the array.
     *
     * @return void
     */
    public function registerEngineResolver()
    {
        $this->app->singleton('view.engine.resolver', function () {
            $resolver = new EngineResolver;

            // Next we will register the various engines with the resolver so that the
            // environment can resolve the engines it needs for various views based
            // on the extension of view files. We call a method for each engines.
            foreach ($this->engines as $engine) {
                $this->{'register'.ucfirst($engine).'Engine'}($resolver);
            }

            return $resolver;
        });
    }

    /**
     * Register the Antlers engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerAntlersEngine($resolver)
    {
        $resolver->register('antlers', function () {
            return new AntlersEngine($this->app['files']);
        });

        $this->app->singleton('antlers.view.parser', function ($app) {
            $parser = new Parser;
            $parser->cumulativeNoparse(true);
            return $parser;
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $this->app->bind('view.finder', function ($app) {
            return new FileViewFinder($app['files'], $app['config']['view.paths']);
        });
    }
}
