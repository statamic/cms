<?php

namespace Statamic\StaticCaching;

use Facades\Statamic\View\Cascade;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\StaticCaching\NoCache\Session;

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

        $this->app->singleton(Session::class, function ($app) {
            $uri = $app['request']->getUri();

            if (config('statamic.static_caching.ignore_query_strings', false)) {
                $uri = explode('?', $uri)[0];
            }

            return new Session($uri);
        });

        $this->app->bind(UrlExcluder::class, function ($app) {
            $class = config('statamic.static_caching.exclude.class') ?? DefaultUrlExcluder::class;

            return $app[$class];
        });

        $this->app->bind(DefaultUrlExcluder::class, function ($app) {
            $config = $app['config']['statamic.static_caching.exclude'];

            // Before the urls sub-array was introduced, you could define
            // the urls to be excluded at the top "exclude" array level.
            $urls = $config['urls'] ?? $config;

            return new DefaultUrlExcluder(
                $app[Cacher::class]->getBaseUrl(),
                $urls
            );
        });
    }

    public function boot()
    {
        if (config('statamic.static_caching.strategy')) {
            Event::subscribe(Invalidate::class);
        }

        // When the cascade gets hydrated, insert it into the
        // nocache session so it can filter out contextual data.
        Cascade::hydrated(function ($cascade) {
            $this->app[Session::class]->setCascade($cascade->toArray());
        });

        Blade::directive('nocache', function ($exp) {
            return '<?php echo app("Statamic\StaticCaching\NoCache\BladeDirective")->handle('.$exp.', \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\'])); ?>';
        });
    }
}
