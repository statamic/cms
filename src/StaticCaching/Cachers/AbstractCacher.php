<?php

namespace Statamic\StaticCaching\Cachers;

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Statamic\Console\Commands\StaticWarmJob;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\RecacheToken;
use Statamic\StaticCaching\UrlExcluder;
use Statamic\Support\Str;

abstract class AbstractCacher implements Cacher
{
    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $config;

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
        // Caches written before domains were tracked per site only have the flat list.
        return collect($this->cache->get($this->normalizeKey('domains'), []))
            ->merge($this->getSiteDomains()->flatten())
            ->unique()
            ->values();
    }

    /**
     * Cache the current domain.
     *
     * @return void
     */
    public function cacheDomain($domain = null)
    {
        $this->cacheSiteDomain($domain ?? $this->getBaseUrl(), Site::current()->handle());
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

    public function getGeneratedAt(string $url): ?int
    {
        return null;
    }

    /**
     * Get the domains each site has been cached under, keyed by site handle.
     */
    protected function getSiteDomains(): Collection
    {
        return collect($this->cache->get($this->normalizeKey('site-domains'), []));
    }

    /**
     * Flush all the cached URLs.
     *
     * @return void
     */
    public function flushUrls()
    {
        $this->getDomains()->each(function ($domain) {
            $this->cache->forget($this->getUrlsCacheKey($domain));
        });

        $this->cache->forget($this->normalizeKey('domains'));
        $this->cache->forget($this->normalizeKey('site-domains'));
    }

    protected function flushDedicatedStore(): void
    {
        if (! config()->has('cache.stores.static_cache')) {
            return;
        }

        if ($this->cache === Cache::store('static_cache')) {
            $this->cache->flush();
        }
    }

    /**
     * Save a URL to the cache.
     *
     * @return void
     */
    public function cacheUrl($key, $url, $domain = null)
    {
        $domain = $domain ?? $this->getBaseUrl();

        $url = Str::removeLeft($url, $domain);

        $this->withUrlsLock(function () use ($key, $url, $domain) {
            $this->cacheSiteDomain($domain, (Site::findByUrl($domain.$url) ?? Site::default())->handle());

            $urls = $this->getUrls($domain)->put($key, $url);

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
        $this->forgetUrls([$key], $domain);
    }

    /**
     * Forget / remove multiple URLs from the cache by their keys.
     *
     * @param  array  $keys
     * @param  string|null  $domain
     * @return void
     */
    public function forgetUrls($keys, $domain = null)
    {
        if (empty($keys)) {
            return;
        }

        $this->withUrlsLock(function () use ($keys, $domain) {
            $urls = $this->getUrls($domain)->except($keys);

            $this->cache->forever($this->getUrlsCacheKey($domain), $urls->all());
        });
    }

    private function cacheSiteDomain(string $domain, string $site): void
    {
        $siteDomains = $this->getSiteDomains();

        if (in_array($domain, $domains = $siteDomains->get($site, []))) {
            return;
        }

        $this->cache->forever($this->normalizeKey('site-domains'), $siteDomains->put($site, [...$domains, $domain])->all());
    }

    private function withUrlsLock(callable $callback)
    {
        if (! ($store = $this->cache->getStore()) instanceof LockProvider) {
            return $callback();
        }

        try {
            return $store->lock($this->normalizeKey('urls'), 10)->block(10, $callback);
        } catch (LockTimeoutException $e) {
            return $callback();
        }
    }

    /**
     * Invalidate a wildcard URL.
     */
    protected function invalidateWildcardUrl($wildcard)
    {
        // Remove the asterisk
        $wildcard = substr($wildcard, 0, -1);

        [$wildcard, $domain] = $this->getPathAndDomain($wildcard);

        $this->getWildcardMatches($wildcard, $domain)->each(function (string $url) use ($domain) {
            $this->invalidateUrl($url, $domain);
        });
    }

    /**
     * Invalidate multiple URLs.
     *
     * @param  array  $urls
     * @return void
     */
    public function invalidateUrls($urls)
    {
        collect($urls)->each(function ($url) {
            if (Str::contains($url, '*')) {
                $this->invalidateWildcardUrl($url);
            } else {
                $this->invalidateUrl(...$this->getPathAndDomain($url));
            }
        });
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
     * @param  string  $url
     * @param  string|null  $domain
     * @return void
     */
    public function refreshUrl($url, $domain = null)
    {
        $domain = $domain ?: $this->getBaseUrl();

        $this->getInvalidationDomains($url, $domain)->each(function (string $target) use ($url, $domain) {
            $this->getUrls($target)->filter(function (string $value) use ($url): bool {
                return $value === $url || Str::startsWith($value, $url.'?');
            })->each(function (string $url, string $key) use ($target, $domain) {
                // Other domains were recorded from request hosts, which can be spoofed, so they're never
                // requested. Warming an error response would just fail with the same error. In both
                // cases, invalidate it instead and let the next request cache a fresh copy.
                if ($target !== $domain || $this->hasCachedErrorResponse($key)) {
                    $this->invalidateUrl($url, $target);

                    return;
                }

                $url = RecacheToken::addToUrl($domain.$url);

                $request = new GuzzleRequest('GET', $url);

                $job = config('statamic.static_caching.warm_job') ?? StaticWarmJob::class;

                $job::dispatch($request, [])
                    ->onConnection(config('statamic.static_caching.warm_queue_connection') ?? config('queue.default'))
                    ->onQueue(config('statamic.static_caching.warm_queue'));
            });
        });
    }

    /**
     * Check if the cached response for a URL key is an error response.
     *
     * @param  string  $key
     * @return bool
     */
    protected function hasCachedErrorResponse($key)
    {
        return false;
    }

    /**
     * Refresh a wildcard URL.
     */
    protected function refreshWildcardUrl($wildcard)
    {
        // Remove the asterisk
        $wildcard = substr($wildcard, 0, -1);

        [$wildcard, $domain] = $this->getPathAndDomain($wildcard);

        $this->getWildcardMatches($wildcard, $domain)->each(function (string $url) use ($domain) {
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

    /**
     * Get the domains a URL should be invalidated under.
     *
     * Pages are tracked under the domain they were requested from, which can differ
     * from the domain the URL resolves to in this process. e.g. a queue worker has
     * no request, so a relative site URL resolves against the app url instead. So
     * the domains the URL's site has actually been cached under are included too.
     */
    protected function getInvalidationDomains(string $url, ?string $domain = null, ?Collection $siteDomains = null): Collection
    {
        $domain = $domain ?: $this->getBaseUrl();

        if (is_null($site = Site::findByUrl($domain.$url))) {
            return collect([$domain]);
        }

        $siteDomains ??= $this->getSiteDomains();

        return collect([$domain, ...$siteDomains->get($site->handle(), [])])->unique()->values();
    }

    /**
     * Get the cached URLs matching a wildcard across its invalidation domains.
     *
     * The domain's own matches are returned as-is, followed by any other
     * matches the remaining domains have that aren't already included.
     */
    protected function getWildcardMatches(string $wildcard, ?string $domain = null, ?callable $urls = null, ?Collection $siteDomains = null): Collection
    {
        $urls ??= fn (string $domain): Collection => $this->getUrls($domain);

        return $this->getInvalidationDomains($wildcard, $domain, $siteDomains)->reduce(function (Collection $matches, string $domain, int $index) use ($urls, $wildcard): Collection {
            return $matches->concat(collect($urls($domain))
                ->filter(fn (string $url): bool => Str::startsWith($url, $wildcard))
                ->reject(fn (string $url): bool => $index > 0 && $matches->contains($url))
                ->values());
        }, collect());
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
