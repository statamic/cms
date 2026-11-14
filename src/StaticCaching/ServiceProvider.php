<?php

namespace Statamic\StaticCaching;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Illuminate\Support\Str;
use Statamic\Events\CollectionDeleted;
use Statamic\Events\CollectionSaved;
use Statamic\Events\CollectionTreeDeleted;
use Statamic\Events\CollectionTreeSaved;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;
use Statamic\Events\EntryScheduleReached;
use Statamic\Events\LocalizedTermDeleted;
use Statamic\Events\LocalizedTermSaved;
use Statamic\Events\SiteDeleted;
use Statamic\Events\SiteSaved;
use Statamic\Events\StacheCleared;
use Statamic\Events\StaticCacheCleared;
use Statamic\Events\TaxonomyDeleted;
use Statamic\Events\TaxonomySaved;
use Statamic\Events\TermDeleted;
use Statamic\Events\TermSaved;
use Statamic\Events\UrlInvalidated;
use Statamic\Facades\Cascade;
use Statamic\Facades\Site;
use Statamic\StaticCaching\NoCache\DatabaseSession;
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
            $uri = $app[Cacher::class]->getUrl($app['request']);

            if (config('statamic.static_caching.ignore_query_strings', false)) {
                $uri = explode('?', $uri)[0];
            }

            if (Str::contains($uri, '?')) {
                $uri = Str::before($uri, '?').'?'.Request::normalizeQueryString(Str::after($uri, '?'));
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
        Event::listen([
            EntrySaved::class,
            EntryDeleted::class,
            EntryScheduleReached::class,
            CollectionSaved::class,
            CollectionDeleted::class,
            CollectionTreeSaved::class,
            CollectionTreeDeleted::class,
            TaxonomySaved::class,
            TaxonomyDeleted::class,
            TermSaved::class,
            TermDeleted::class,
            LocalizedTermSaved::class,
            LocalizedTermDeleted::class,
            SiteSaved::class,
            SiteDeleted::class,
            StacheCleared::class,
        ], [WarmableUrls::class, 'clearCache']);

        Event::listen([UrlInvalidated::class, StaticCacheCleared::class], [CachedPages::class, 'clearCache']);

        if (config('statamic.static_caching.strategy')) {
            Event::subscribe(Invalidate::class);
        }

        // When the cascade gets hydrated, insert it into the
        // nocache session so it can filter out contextual data.
        Cascade::hydrated(function ($cascade) {
            app(Session::class)->setCascade($cascade->toArray());
        });

        Blade::directive('nocache', function ($exp) {
            return '<?php echo app("Statamic\StaticCaching\NoCache\BladeDirective")->handle('.$exp.', \Illuminate\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\'])); ?>';
        });

        Request::macro('normalizedFullUrl', function () {
            return app(Cacher::class)->getUrl($this);
        });

        Request::macro('fakeStaticCacheStatus', function (int $status) {
            // Namespace the shared error by the current site so that multisite
            // installs serve a correctly localized error page per site, rather
            // than whichever site happened to render the error first.
            $url = '/__shared-errors/'.Site::current()->handle().'/'.$status;
            $this->pathInfo = $url;
            $this->requestUri = $url;
            app(Session::class)->setUrl($url);

            return $this;
        });

        $this->app[ResponseStatusTracker::class]->registerMacros();
    }
}
