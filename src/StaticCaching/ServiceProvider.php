<?php

namespace Statamic\StaticCaching;

use Facades\Statamic\View\Cascade;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\StaticCaching\NoCache\NoCacheManager;
use Statamic\StaticCaching\Replacers\NoCacheReplacer;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->singleton(StaticCacheManager::class, function ($app) {
            return new StaticCacheManager($app);
        });

        $this->app->bind(Cacher::class, function ($app) {
            return $app[StaticCacheManager::class]->driver();
        });

        $this->app->bind(Invalidator::class, function ($app) {
            $class = config('statamic.static_caching.invalidation.class') ?? DefaultInvalidator::class;

            return $app[$class];
        });

        $this->app->bind(DefaultInvalidator::class, function ($app) {
            return new DefaultInvalidator(
                $app[Cacher::class],
                $app['config']['statamic.static_caching.invalidation.rules']
            );
        });

        $this->app->singleton(NoCacheManager::class, function ($app) {
            return new NoCacheManager;
        });
    }

    public function boot()
    {
        if (config('statamic.static_caching.strategy')) {
            Event::subscribe(Invalidate::class);
        }

        $this->addNocacheReplacer();

        // When the cascade gets hydrated, insert it into the
        // nocache session so it can filter out contextual data.
        Cascade::hydrated(function ($cascade) {
            $this->app[NoCacheManager::class]->session()->setCascade($cascade->toArray());
        });

        Blade::directive('nocache', function ($exp) {
            return '<?php echo app("Statamic\StaticCaching\NoCache\BladeDirective")->handle('.$exp.', $__data); ?>';
        });
    }

    private function addNocacheReplacer()
    {
        $configKey = 'statamic.static_caching.replacers';
        $replacers = config($configKey);
        $replacers[] = NoCacheReplacer::class;
        config([$configKey => $replacers]);
    }
}
