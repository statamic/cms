<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Events\ResponsePrepared;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;

class ApplicationCacher extends AbstractCacher
{
    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * @var string|null
     */
    private $cached;

    /**
     * Cache a page.
     *
     * @param  \Illuminate\Http\Request  $request  Request associated with the page to be cached
     * @param  string  $content  The response content to be cached
     */
    public function cachePage(Request $request, $content)
    {
        $url = $this->getUrl($request);

        if ($this->isExcluded($url)) {
            return;
        }

        // Get a hashed version of the URL for the key since slashes
        // and other URL characters wouldn't work as a cache key.
        $key = $this->makeHash($url);

        // Keep track of the URL and key the response content is about to be stored within.
        $this->cacheUrl($key, ...$this->getPathAndDomain($url));

        $responseKey = $this->normalizeKey('responses:'.$key);
        $headersKey = $this->normalizeKey('headers:'.$key);
        $value = $this->normalizeContent($content);

        if ($value instanceof JsonResponse) {
            $value = $value->getContent();
        }

        if ($expiration = $this->getDefaultExpiration()) {
            $this->cache->put($responseKey, $value, now()->addMinutes($expiration));
        } else {
            $this->cache->forever($responseKey, $value);
        }

        Event::listen(ResponsePrepared::class, function (ResponsePrepared $event) use ($headersKey, $expiration) {
            $headers = collect($event->response->headers->all())
                ->reject(fn ($value, $key) => in_array($key, ['date', 'x-powered-by', 'cache-control', 'expires', 'set-cookie']))
                ->mapWithKeys(fn ($value, $key) => [$key => Arr::first($value)])
                ->all();

            $this->cache->put($headersKey, $headers, $expiration);
        });
    }

    /**
     * Check if a page has been cached.
     *
     * @return bool
     */
    public function hasCachedPage(Request $request)
    {
        return (bool) $this->cached = $this->getFromCache($request);
    }

    /**
     * Get a cached page.
     *
     * @return string
     */
    public function getCachedPage(Request $request)
    {
        $cachedPage = $this->cached ?? $this->getFromCache($request);

        return $cachedPage;
    }

    public function getCachedHeaders(Request $request)
    {
        $url = $this->getUrl($request);

        $key = $this->makeHash($url);

        return $this->cache->get($this->normalizeKey('headers:'.$key));
    }

    private function getFromCache(Request $request)
    {
        $url = $this->getUrl($request);

        $key = $this->makeHash($url);

        return $this->cache->get($this->normalizeKey('responses:'.$key));
    }

    /**
     * Flush out the entire static cache.
     *
     * @return void
     */
    public function flush()
    {
        $this->getDomains()->each(function ($domain) {
            $this->getUrls($domain)->keys()->each(function ($key) {
                $this->cache->forget($this->normalizeKey('responses:'.$key));
            });
        });

        $this->flushUrls();
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
        $this
            ->getUrls($domain)
            ->filter(fn ($value) => $value === $url || str_starts_with($value, $url.'?'))
            ->each(function ($value, $key) {
                $this->cache->forget($this->normalizeKey('responses:'.$key));
                $this->forgetUrl($key);
            });
    }
}
