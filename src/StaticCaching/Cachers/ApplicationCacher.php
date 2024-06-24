<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Http\Request;
use Illuminate\Routing\Events\ResponsePrepared;
use Illuminate\Support\Facades\Event;
use Statamic\Events\UrlInvalidated;
use Statamic\StaticCaching\Page;

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

        $key = $this->normalizeKey('responses:'.$key);
        $value = $this->normalizeContent($content);

        Event::listen(ResponsePrepared::class, function (ResponsePrepared $event) use ($key, $value) {
            $headers = collect($event->response->headers->all())
                ->reject(fn ($value, $key) => in_array($key, ['date', 'x-powered-by', 'cache-control', 'expires', 'set-cookie']))
                ->all();

            $cacheValue = [
                'content' => $value,
                'headers' => $headers,
                'status' => $event->response->getStatusCode(),
            ];

            $this->getDefaultExpiration()
                ? $this->cache->put($key, $cacheValue, now()->addMinutes($this->getDefaultExpiration()))
                : $this->cache->forever($key, $cacheValue);
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
     * @return Page
     */
    public function getCachedPage(Request $request)
    {
        $cachedPage = $this->cached ?? $this->getFromCache($request);

        return new Page($cachedPage['content'], $cachedPage['headers'], $cachedPage['status'] ?? 200);
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

        UrlInvalidated::dispatch($url, $domain);
    }
}
