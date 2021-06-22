<?php

namespace Statamic\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Statamic\Facades\Site;
use Statamic\View\Antlers\Engine;
use Statamic\View\Antlers\Parser;
use Statamic\View\Cascade;
use Statamic\View\Store;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Store::class);

        $this->app->singleton(Cascade::class, function ($app) {
            return new Cascade($app['request'], Site::current());
        });

        $this->app->bind(Parser::class, function ($app) {
            return (new Parser)
                ->callback([Engine::class, 'renderTag'])
                ->cascade($app[Cascade::class]);
        });

        $this->app->singleton(Engine::class, function ($app) {
            return new Engine($app['files'], $app[Parser::class]);
        });
    }

    public function boot()
    {
        View::macro('withoutExtractions', function () {
            if ($this->engine instanceof Engine) {
                $this->engine->withoutExtractions();
            }

            return $this;
        });

        foreach (Engine::EXTENSIONS as $extension) {
            $this->app['view']->addExtension($extension, 'antlers', function () {
                return $this->app[Engine::class];
            });
        }

        ini_set('pcre.backtrack_limit', config('statamic.system.pcre_backtrack_limit', -1));
    }
}
