<?php

namespace Statamic\StaticCaching\Cachers;

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Statamic\Console\Commands\StaticWarmJob;
use Statamic\Events\UrlInvalidated;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\RecacheToken;
use Statamic\StaticCaching\UrlExcluder;
use Statamic\Support\Str;

abstract class AbstractCacher implements Cacher
{
    /**
     * Seconds until a held lock auto-expires, as a crash safety net.
     */
    protected const LOCK_TIMEOUT = 10;

    /**
     * Seconds a caller will wait to acquire a lock before giving up.
     */
    protected const LOCK_WAIT = 5;

    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $config;

    /**
     * Lock keys currently held by this instance, so nested withLock() calls
     * for the same key (e.g. forgetUrl() called from code already holding the
     * domain's urls lock) don't try to re-acquire a lock against themselves
     * and deadlock until LOCK_WAIT expires.
     *
     * @var array<string, bool>
     */
    private $heldLocks = [];

    public function __construct(Repository $cache, $config)
    {
        $this->cache = $cache;
        $this->config = collect($config);
    }

    /**
     * Get a config value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function config($key, $default = null)
    {
        return $this->config->get($key, $default);
    }

    /**
     * Get the base URL (domain).
     *
     * @return string
     */
    public function getBaseUrl()
    {
        // Check 'base_url' for backward compatibility.
        if (! $baseUrl = $this->config('base_url')) {
            // This could potentially just be Site::current()->absoluteUrl() but at the
            // moment that method gets the URL based on the request. For now, we will
            // manually get it from the config, as to not break any existing sites.
            $baseUrl = URL::isAbsolute($url = Site::current()->url())
                ? $url
                : config('app.url').$url;
        }

        return URL::tidy($baseUrl, external: true, withTrailingSlash: false);
    }

    /**
     * @return int
     */
    public function getDefaultExpiration()
    {
        return (int) $this->config('expiry');
    }

    /**
     * @param  mixed  $content
     * @return string
     */
    protected function normalizeContent($content)
    {
        if ($content instanceof Response || $content instanceof JsonResponse) {
            $content = $content->content();
        }

        return $content;
    }

    /**
     * Prefix a cache key.
     *
     * @param  string  $key
     * @return string
     */
    protected function normalizeKey($key)
    {
        return "static-cache:$key";
    }

    /**
     * Get a hashed string representation of a URL.
     *
     * @param  string  $url
     * @return string
     */
    protected function makeHash($url)
    {
        return md5($url);
    }

    /**
     * Get the domains that have been cached.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDomains()
    {
        return collect($this->cache->get($this->normalizeKey('domains'), []));
    }

    /**
     * Cache the current domain.
     *
     * @return void
     */
    public function cacheDomain($domain = null)
    {
        $domain = $domain ?? $this->getBaseUrl();

        $this->withLock($this->normalizeKey('domains:lock'), function () use ($domain) {
            $domains = $this->getDomains();

            if (! $domains->contains($domain)) {
                $domains->push($domain);
            }

            $this->cache->forever($this->normalizeKey('domains'), $domains->all());
        });
    }

    /**
     * Get all the URLs that have been cached.
     *
     * @param  string|null  $domain
     * @return \Illuminate\Support\Collection
     */
    public function getUrls($domain = null)
    {
        $key = $this->getUrlsCacheKey($domain);

        return collect($this->cache->get($key, []));
    }

    /**
     * Flush all the cached URLs.
     *
     * @return void
     */
    public function flushUrls()
    {
        $this->getDomains()->each(function ($domain) {
            $this->withLock($this->getUrlsLockKey($domain), function () use ($domain) {
                $this->cache->forget($this->getUrlsCacheKey($domain));
            });
        });

        $this->cache->forget($this->normalizeKey('domains'));
    }

    /**
     * Save a URL to the cache.
     *
     * @param  string  $key
     * @param  string  $url
     * @return void
     */
    public function cacheUrl($key, $url, $domain = null)
    {
        $domain = $domain ?? $this->getBaseUrl();

        $this->cacheDomain($domain);

        $this->withLock($this->getUrlsLockKey($domain), function () use ($key, $url, $domain) {
            $urls = $this->getUrls($domain);

            $url = Str::removeLeft($url, $domain);

            $urls->put($key, $url);

            $this->cache->forever($this->getUrlsCacheKey($domain), $urls->all());
        });
    }

    /**
     * Forget / remove a URL from the cache by its key.
     *
     * @param  string  $key
     * @return void
     */
    public function forgetUrl($key, $domain = null)
    {
        $this->withLock($this->getUrlsLockKey($domain), function () use ($key, $domain) {
            $urls = $this->getUrls($domain);

            $urls->forget($key);

            $this->cache->forever($this->getUrlsCacheKey($domain), $urls->all());
        });
    }

    /**
     * Run a callback while holding an exclusive lock for the given key, if the
     * configured cache store supports locking. Falls back to running the
     * callback unprotected if it doesn't, so cache stores without lock
     * support (e.g. some third-party addons) don't hard crash.
     *
     * @return mixed
     */
    protected function withLock(string $key, \Closure $callback)
    {
        if (isset($this->heldLocks[$key])) {
            return $callback();
        }

        if (! $this->cache->getStore() instanceof LockProvider) {
            return $callback();
        }

        return $this->cache->lock($key, static::LOCK_TIMEOUT)->block(static::LOCK_WAIT, function () use ($key, $callback) {
            $this->heldLocks[$key] = true;

            try {
                return $callback();
            } finally {
                unset($this->heldLocks[$key]);
            }
        });
    }

    /**
     * @param  string|null  $domain
     * @return string
     */
    protected function getUrlsLockKey($domain = null)
    {
        return $this->getUrlsCacheKey($domain).':lock';
    }

    /**
     * Invalidate a URL.
     *
     * @param  string  $url
     * @param  string|null  $domain
     * @return void
     */
    public function invalidateUrl($url, $domain = null)
    {
        // For CLI contexts where Site::current()->url() may return the wrong
        // domain causing getUrls() to look under the wrong cache key.
        if ($domain === null) {
            [$url, $domain] = $this->getPathAndDomain($url);
        }

        $this->invalidatePathsForDomain([$url], $domain);
    }

    /**
     * Invalidate a wildcard URL.
     *
     * @param  string  $wildcard
     */
    protected function invalidateWildcardUrl($wildcard)
    {
        [, $domain] = $this->getPathAndDomain(substr($wildcard, 0, -1));

        $this->invalidatePathsForDomain([$wildcard], $domain);
    }

    /**
     * Invalidate multiple URLs.
     *
     * @param  array  $urls
     * @return void
     */
    public function invalidateUrls($urls)
    {
        collect($urls)
            ->groupBy(fn ($url) => $this->resolveDomainForInvalidation($url))
            ->each(fn ($urlsForDomain, $domain) => $this->invalidatePathsForDomain($urlsForDomain->all(), $domain));
    }

    /**
     * Invalidate a set of paths (and trailing-* wildcards) for a single domain.
     *
     * Two phases, so the urls lock is only held for map bookkeeping and never
     * for driver cleanup (file deletes, cache forgets) or event listeners:
     *
     * 1. Under the domain's urls lock: resolve which map entries match, remove
     *    them, and persist the map in a single write.
     * 2. After releasing the lock: driver cleanup and UrlInvalidated events.
     *
     * A page cached concurrently with phase two can at worst leave a map entry
     * whose cached copy was just deleted, which is self-healing: the next
     * request re-renders and re-caches under the same key. The reverse - a
     * cached copy the map doesn't know about - cannot happen.
     *
     * @param  array  $urls
     * @param  string|null  $domain
     * @return void
     */
    protected function invalidatePathsForDomain($urls, $domain)
    {
        $paths = collect($urls)->map(function ($url) {
            $wildcard = Str::contains($url, '*');

            [$path] = $this->getPathAndDomain($wildcard ? substr($url, 0, -1) : $url);

            return ['path' => $path, 'wildcard' => $wildcard];
        });

        $invalidated = $this->withLock($this->getUrlsLockKey($domain), function () use ($paths, $domain) {
            $urls = $this->getUrls($domain);

            $invalidated = $urls->filter(fn ($value) => $paths->contains(fn ($path) => $path['wildcard']
                ? Str::startsWith($value, $path['path'])
                : ($value === $path['path'] || Str::startsWith($value, $path['path'].'?'))));

            if ($invalidated->isNotEmpty()) {
                $this->cache->forever($this->getUrlsCacheKey($domain), $urls->diffKeys($invalidated)->all());
            }

            return $invalidated;
        });

        $this->cleanupInvalidatedUrls($invalidated, $paths, $domain);

        $paths->each(function ($path) use ($invalidated, $domain) {
            $path['wildcard']
                ? $invalidated
                    ->filter(fn ($value) => Str::startsWith($value, $path['path']))
                    ->each(fn ($value) => UrlInvalidated::dispatch($value, $domain))
                : UrlInvalidated::dispatch($path['path'], $domain);
        });
    }

    /**
     * Clean up the driver's stored copies of invalidated pages. Runs after the
     * urls lock has been released.
     *
     * @param  \Illuminate\Support\Collection  $invalidated  The removed entries, keyed by their urls map key.
     * @param  \Illuminate\Support\Collection  $paths  The ['path' => string, 'wildcard' => bool] entries the invalidation was requested with.
     * @param  string|null  $domain
     * @return void
     */
    abstract protected function cleanupInvalidatedUrls($invalidated, $paths, $domain);

    /**
     * Resolve the domain an invalidation entry belongs to, the same way
     * invalidateUrl()/invalidateWildcardUrl() would internally, so entries can
     * be grouped by domain before either is called.
     *
     * @param  string  $url
     * @return string
     */
    protected function resolveDomainForInvalidation($url)
    {
        $url = Str::contains($url, '*') ? substr($url, 0, -1) : $url;

        [, $domain] = $this->getPathAndDomain($url);

        return $domain;
    }

    /**
     * Refresh multiple URLs.
     *
     * @param  array  $urls
     * @return void
     */
    public function refreshUrls($urls)
    {
        collect($urls)->each(function ($url) {
            if (Str::contains($url, '*')) {
                $this->refreshWildcardUrl($url);
            } else {
                $this->refreshUrl(...$this->getPathAndDomain($url));
            }
        });
    }

    /**
     * Refresh an individual URL.
     *
     * @param  string  $path
     * @param  string|null  $domain
     * @return void
     */
    public function refreshUrl($url, $domain = null)
    {
        $this->getUrls($domain)->filter(function ($value) use ($url) {
            return $value === $url || Str::startsWith($value, $url.'?');
        })->each(function ($url) use ($domain) {
            $url = ($domain ?: $this->getBaseUrl()).$url;

            $url = RecacheToken::addToUrl($url);

            $request = new GuzzleRequest('GET', $url);

            StaticWarmJob::dispatch($request, [])
                ->onConnection(config('statamic.static_caching.warm_queue_connection') ?? config('queue.default'))
                ->onQueue(config('statamic.static_caching.warm_queue'));
        });
    }

    /**
     * Refresh a wildcard URL.
     *
     * @param  string  $wildcard
     */
    protected function refreshWildcardUrl($wildcard)
    {
        // Remove the asterisk
        $wildcard = substr($wildcard, 0, -1);

        [$wildcard, $domain] = $this->getPathAndDomain($wildcard);

        $this->getUrls($domain)->filter(function ($url) use ($wildcard) {
            return Str::startsWith($url, $wildcard);
        })->each(function ($url) use ($domain) {
            $this->refreshUrl($url, $domain);
        });
    }

    /**
     * Determine if a given URL should be excluded from caching.
     *
     * @param  string  $url
     * @return bool
     */
    public function isExcluded($url)
    {
        return app(UrlExcluder::class)->isExcluded($url);
    }

    /**
     * @param  string|null  $domain
     * @return string
     */
    protected function getUrlsCacheKey($domain = null)
    {
        $domain = $domain ?: $this->getBaseUrl();

        return $this->normalizeKey($this->makeHash($domain).'.urls');
    }

    public function hasCachedPage(Request $request)
    {
        return $this->getCachedPage($request) !== null;
    }

    protected function getPathAndDomain($url)
    {
        $parsed = parse_url($url);

        if (! isset($parsed['scheme'])) {
            return [
                Str::ensureLeft($url, '/'),
                $this->getBaseUrl(),
            ];
        }

        $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';

        $path = $parsed['path'] ?? '/';

        return [
            $path.$query,
            $parsed['scheme'].'://'.$parsed['host'],
        ];
    }

    protected function removeBackgroundRecacheTokenFromUrl(string $url): string
    {
        if (! config('statamic.static_caching.background_recache', false)) {
            return $url;
        }

        return RecacheToken::removeFromUrl($url);
    }

    public function getUrl(Request $request)
    {
        $url = $this->removeBackgroundRecacheTokenFromUrl($request->getUri());

        if ($this->isExcluded($url)) {
            return $url;
        }

        if ($this->config('ignore_query_strings', false)) {
            $url = explode('?', $url)[0];
        }

        $parts = parse_url($url);

        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);

            if ($allowedQueryStrings = $this->config('allowed_query_strings')) {
                $query = array_intersect_key($query, array_flip($allowedQueryStrings));
            }

            if ($disallowedQueryStrings = $this->config('disallowed_query_strings')) {
                $disallowedQueryStrings = array_flip($disallowedQueryStrings);
                $query = array_diff_key($query, $disallowedQueryStrings);
            }

            $url = $parts['scheme'].'://'.$parts['host'].$parts['path'];

            if ($query) {
                $url .= '?'.http_build_query($query);
            }
        }

        return $url;
    }
}
