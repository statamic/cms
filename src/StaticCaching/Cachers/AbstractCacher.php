<?php

namespace Statamic\StaticCaching\Cachers;

use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Statamic\Console\Commands\StaticWarmJob;
use Statamic\Facades\Site;
use Statamic\Facades\StaticCache;
use Statamic\Facades\URL;
use Statamic\StaticCaching\Cacher;
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
        return collect($this->cache->get($this->normalizeKey('domains'), []));
    }

    /**
     * Cache the current domain.
     *
     * @return void
     */
    public function cacheDomain($domain = null)
    {
        $domains = $this->getDomains();

        if (! $domains->contains($domain = $domain ?? $this->getBaseUrl())) {
            $domains->push($domain);
        }

        $this->cache->forever($this->normalizeKey('domains'), $domains->all());
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
            $this->cache->forget($this->getUrlsCacheKey($domain));
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

        $urls = $this->getUrls($domain);

        $url = Str::removeLeft($url, $domain);

        $urls->put($key, $url);

        $this->cache->forever($this->getUrlsCacheKey($domain), $urls->all());
    }

    /**
     * Forget / remove a URL from the cache by its key.
     *
     * @param  string  $key
     * @return void
     */
    public function forgetUrl($key, $domain = null)
    {
        $urls = $this->getUrls($domain);

        $urls->forget($key);

        $this->cache->forever($this->getUrlsCacheKey($domain), $urls->all());
    }

    /**
     * Invalidate a wildcard URL.
     *
     * @param  string  $wildcard
     */
    protected function invalidateWildcardUrl($wildcard)
    {
        // Remove the asterisk
        $wildcard = substr($wildcard, 0, -1);

        [$wildcard, $domain] = $this->getPathAndDomain($wildcard);

        $this->getUrls($domain)->filter(function ($url) use ($wildcard) {
            return Str::startsWith($url, $wildcard);
        })->each(function ($url) use ($domain) {
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

            if (Str::endsWith($url, '?')) {
                $url = Str::removeRight($url, '?');
            }

            $url .= (str_contains($url, '?') ? '&' : '?').'__recache='.StaticCache::recacheToken();

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

    protected function removeBackgroundRecacheTokenFromUrl(Request $request, string $url): string
    {
        if (! config('statamic.static_caching.background_recache', false)) {
            return $url;
        }

        if (! $recache = $request->input('__recache')) {
            return $url;
        }

        $url = str_replace('__recache='.urlencode($recache), '', $url);
        if (substr($url, -1, 1) == '?') {
            $url = substr($url, 0, -1);
        }

        return $url;
    }

    public function getUrl(Request $request)
    {
        $url = $this->removeBackgroundRecacheTokenFromUrl($request, $request->getUri());

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
