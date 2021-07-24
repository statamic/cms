<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Statamic\StaticCaching\Cacher;
use Statamic\Support\Str;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @param Repository $cache
     */
    public function __construct(Repository $cache, $config)
    {
        $this->cache = $cache;
        $this->config = collect($config);
    }

    /**
     * Get a config value.
     *
     * @param string $key
     * @param mixed $default
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
        return $this->config('base_url') ?? RequestFacade::root();
    }

    /**
     * @return int
     */
    public function getDefaultExpiration()
    {
        return $this->config('expiry')
            ?? $this->config('default_cache_length'); // deprecated
    }

    /**
     * @param  mixed $content
     * @return string
     */
    protected function normalizeContent($content)
    {
        if ($content instanceof Response) {
            $content = $content->content();
        }

        return $content;
    }

    /**
     * Prefix a cache key.
     *
     * @param string $key
     * @return string
     */
    protected function normalizeKey($key)
    {
        return "static-cache:$key";
    }

    /**
     * Get a hashed string representation of a URL.
     *
     * @param string $url
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
    public function cacheDomain()
    {
        $domains = $this->getDomains();

        if (! $domains->contains($domain = $this->getBaseUrl())) {
            $domains->push($domain);
        }

        $this->cache->forever($this->normalizeKey('domains'), $domains->all());
    }

    /**
     * Get the URL from a request.
     *
     * @param Request $request
     * @return string
     */
    public function getUrl(Request $request)
    {
        $url = $request->getUri();

        if ($this->config('ignore_query_strings')) {
            $url = explode('?', $url)[0];
        }

        return $url;
    }

    /**
     * Get all the URLs that have been cached.
     *
     * @param string|null $domain
     * @return \Illuminate\Support\Collection
     */
    public function getUrls($domain = null)
    {
        $domain = $domain ?: $this->getBaseUrl();

        $domain = $this->makeHash($domain);

        return collect($this->cache->get($this->normalizeKey($domain.'.urls'), []));
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
     * @param string $key
     * @param string $url
     * @return void
     */
    public function cacheUrl($key, $url)
    {
        $this->cacheDomain();

        $urls = $this->getUrls();

        $url = Str::removeLeft($url, $this->getBaseUrl());

        $urls->put($key, $url);

        $this->cache->forever($this->getUrlsCacheKey(), $urls->all());
    }

    /**
     * Forget / remove a URL from the cache by its key.
     *
     * @param string $key
     * @return void
     */
    public function forgetUrl($key)
    {
        $urls = $this->getUrls();

        $urls->forget($key);

        $this->cache->forever($this->getUrlsCacheKey(), $urls->all());
    }

    /**
     * Invalidate a wildcard URL.
     *
     * @param string $wildcard
     */
    protected function invalidateWildcardUrl($wildcard)
    {
        // Remove the asterisk
        $wildcard = substr($wildcard, 0, -1);

        $this->getUrls()->filter(function ($url) use ($wildcard) {
            return Str::startsWith($url, $wildcard);
        })->each(function ($url) {
            $this->invalidateUrl($url);
        });
    }

    /**
     * Invalidate multiple URLs.
     *
     * @param array $urls
     * @return void
     */
    public function invalidateUrls($urls)
    {
        collect($urls)->each(function ($url) {
            if (Str::contains($url, '*')) {
                $this->invalidateWildcardUrl($url);
            } else {
                $this->invalidateUrl($url);
            }
        });
    }

    /**
     * Determine if a given URL should be excluded from caching.
     *
     * @param string $url
     * @return bool
     */
    public function isExcluded($url)
    {
        $exclusions = collect($this->config('exclude', []));

        // Query strings should be ignored.
        $url = explode('?', $url)[0];

        $url = Str::removeLeft($url, $this->getBaseUrl());

        foreach ($exclusions as $excluded) {
            if (Str::endsWith($excluded, '*') && Str::startsWith($url, substr($excluded, 0, -1))) {
                return true;
            }

            if ($url === $excluded) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|null $domain
     * @return string
     */
    protected function getUrlsCacheKey($domain = null)
    {
        $domain = $domain ?: $this->getBaseUrl();

        return $this->normalizeKey($this->makeHash($domain).'.urls');
    }

    /**
     * Check if a page can be cached.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function canBeCached(Request $request): bool
    {
        return true;
    }

    /**
     * Determine if a given URL should be excluded from caching.
     *
     * @param \Illuminate\Http\Request $url
     * @return bool
     */
    public function hasCachedPage(Request $request)
    {
        return $this->getCachedPage($request) !== null;
    }

    /**
     * Check if a page should be cached.
     *
     * @param \Illuminate\Http\Request $request
     * @param Response $response
     * @return bool
     */
    public function shouldBeCached(Request $request, Response $response)
    {
        if (! $this->canBeCached($request)) {
            return false;
        }

        // Draft and private pages should not be cached.
        if ($response->headers->has('X-Statamic-Draft') || $response->headers->has('X-Statamic-Private')) {
            return false;
        }

        if ($response->getStatusCode() !== 200 || $response->getContent() == '') {
            return false;
        }

        return true;
    }

    /**
     * Check if the cache can be bypassed.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function canBeBypassed()
    {
        return false;
    }

    /**
     * Check if the cache should be bypassed.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function shouldBeBypassed()
    {
        return false;
    }

    /**
     * Return the response from the cache, or dont.
     *
     * @param \Illuminate\Http\Request $request
     * @param Response $response
     * @return Response
     */
    public function response(Request $request, Response $response)
    {
        if ($this->canBeBypassed() && $this->shouldBeBypassed()) {
            // Cache will be bypassed.
            $status = 'BYPASS';
        } elseif ($this->canBeCached($request) && $this->hasCachedPage($request)) {
            // Page is cached, use cache.
            $response->setContent($this->getCachedPage($request));
            $status = 'HIT';
        } elseif ($this->canBeCached($request) && ! $this->hasCachedPage($request)) {
            // Page is not cached, cache it.
            $this->cachePage($request, $response);
            $status = 'MISS';
        } elseif (! $this->canBeCached($request) || ! $this->shouldBeCached($request, $response)) {
            // Cache will be bypassed.
            $status = 'BYPASS';
        } else {
            // Cache was not hit/used.
            $status = 'MISS';
        }

        if (config('statamic.static_caching.send_static_cache_header') && method_exists($response, 'header')) {
            // Set cache header to: [ HIT | MISS | BYPASS ]
            $response->header(config('statamic.static_caching.static_cache_header', 'X-Statamic-Static-Cache'), $status);
        }

        return $response;
    }
}
