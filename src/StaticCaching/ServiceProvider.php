<?php

namespace Statamic\StaticCaching;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Statamic\Facades\Cascade;
use Statamic\StaticCaching\NoCache\DatabaseSession;
use Statamic\StaticCaching\NoCache\Session;
use Illuminate\Http\Response;

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
            $uri = $app[Cacher::class]->getUrl($app['request']);

            if (config('statamic.static_caching.ignore_query_strings', false)) {
                $uri = explode('?', $uri)[0];
            }

            return match ($driver = config('statamic.static_caching.nocache', 'cache')) {
                'cache' => new Session($uri),
                'database' => new DatabaseSession($uri),
                default => throw new \Exception('Nocache driver ['.$driver.'] is not supported.'),
            };
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

        $this->app->singleton(ResponseStatusTracker::class, fn () => new ResponseStatusTracker);
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

        Request::macro('normalizedFullUrl', function () {
            return app(Cacher::class)->getUrl($this);
        });

        Response::macro('makeCacheControlCacheable', function () {
            $this
                ->setMaxAge(config('statamic.static_caching.max_age', 60))
                ->setSharedMaxAge(config('statamic.static_caching.shared_max_age', config('statamic.static_caching.max_age', 60)))
                ->setStaleWhileRevalidate(config('statamic.static_caching.stale_while_revalidate', 60))
                ->setEtag(md5($this->getContent() ?: ''));
        });

        Request::macro('fakeStaticCacheStatus', function (int $status) {
            $url = '/__shared-errors/'.$status;
            $this->pathInfo = $url;
            $this->requestUri = $url;
            app(Session::class)->setUrl($url);

            return $this;
        });

        $this->app[ResponseStatusTracker::class]->registerMacros();
    }
}
