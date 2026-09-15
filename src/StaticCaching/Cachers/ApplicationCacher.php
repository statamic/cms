<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\ResponsePrepared;
use Illuminate\Support\Facades\Event;
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

        try {
            // Keep track of the URL and key the response content is about to be stored within.
            $this->cacheUrl($key, ...$this->getPathAndDomain($url));
        } catch (LockTimeoutException $e) {
            // The URL couldn't be recorded in the urls map, so don't store the
            // response either. The response will just go out uncached.
            return;
        }

        $key = $this->normalizeKey('responses:'.$key);
        $value = $this->normalizeContent($content);

        // The listener stays registered for the lifetime of the process, so it should
        // only handle the response for the request that's currently being cached.
        // Otherwise, in long-running processes (e.g. Octane, tests) it would
        // re-store this entry using later requests' statuses and headers.
        $handled = false;

        Event::listen(ResponsePrepared::class, function (ResponsePrepared $event) use ($key, $value, &$handled) {
            if ($handled) {
                return;
            }

            $handled = true;

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
     * Check if the cached response for a URL key is an error response.
     *
     * @param  string  $key
     * @return bool
     */
    protected function hasCachedErrorResponse($key)
    {
        $cached = $this->cache->get($this->normalizeKey('responses:'.$key));

        if (! is_array($cached)) {
            return false;
        }

        return ($cached['status'] ?? 200) >= 400;
    }

    /**
     * Flush out the entire static cache.
     *
     * @return void
     */
    public function flush()
    {
        // Capture each domain's response keys and wipe its map under the urls
        // lock, so a page cached mid-flush can't end up as a stored response
        // the map doesn't know about. The responses are forgotten afterwards.
        $keys = $this->getDomains()->flatMap(function ($domain) {
            return $this->withLock($this->getUrlsLockKey($domain), function () use ($domain) {
                $keys = $this->getUrls($domain)->keys();

                $this->cache->forget($this->getUrlsCacheKey($domain));

                return $keys;
            });
        });

        $this->cache->forget($this->normalizeKey('domains'));

        $keys->each(fn ($key) => $this->cache->forget($this->normalizeKey('responses:'.$key)));
    }

    /**
     * Forget the stored responses for invalidated urls map entries.
     *
     * @param  \Illuminate\Support\Collection  $invalidated
     * @param  \Illuminate\Support\Collection  $paths
     * @param  string|null  $domain
     * @return void
     */
    protected function cleanupInvalidatedUrls($invalidated, $paths, $domain)
    {
        $invalidated->keys()->each(fn ($key) => $this->cache->forget($this->normalizeKey('responses:'.$key)));
    }
}
