<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Http\Request;
use Illuminate\Routing\Events\ResponsePrepared;
use Illuminate\Support\Facades\Event;
use Statamic\Events\UrlInvalidated;
use Statamic\StaticCaching\Page;
use Statamic\Support\Str;

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
        // For CLI contexts where Site::current()->url() may return the wrong
        // domain causing getUrls() to look under the wrong cache key.
        if ($domain === null) {
            [$url, $domain] = $this->getPathAndDomain($url);
        }

        $this
            ->getUrls($domain)
            ->filter(fn ($value) => $value === $url || str_starts_with($value, $url.'?'))
            ->each(function ($value, $key) use ($domain) {
                $this->cache->forget($this->normalizeKey('responses:'.$key));
                $this->forgetUrl($key, $domain);
            });

        UrlInvalidated::dispatch($url, $domain);
    }

    /**
     * Invalidate multiple URLs.
     *
     * Invalidating URLs one at a time reads and rewrites the domain's whole URL
     * index for every match, which gets slow when many URLs are invalidated at
     * once on a site with a large index. Instead, the URLs are grouped by domain
     * and each domain's index is rewritten once.
     *
     * @param  array  $urls
     * @return void
     */
    public function invalidateUrls($urls)
    {
        collect($urls)
            ->flatMap(fn ($url) => Str::contains($url, '*')
                ? $this->getPathsAndDomainsMatchingWildcard($url)
                : [$this->getPathAndDomain($url)])
            ->groupBy(fn ($pathAndDomain) => $pathAndDomain[1])
            ->each(fn ($pathsAndDomains, $domain) => $this->invalidatePaths(
                $pathsAndDomains->pluck(0)->unique()->all(),
                $domain
            ));
    }

    private function getPathsAndDomainsMatchingWildcard($wildcard)
    {
        [$wildcard, $domain] = $this->getPathAndDomain(substr($wildcard, 0, -1));

        return $this->getUrls($domain)
            ->filter(fn ($url) => str_starts_with($url, $wildcard))
            ->map(fn ($url) => [$url, $domain])
            ->values()
            ->all();
    }

    private function invalidatePaths(array $paths, string $domain)
    {
        $lookup = array_flip($paths);

        // Same matching as invalidateUrl(): the path itself, or any query string version of it.
        $keys = $this->getUrls($domain)
            ->filter(function ($value) use ($lookup) {
                if (isset($lookup[$value])) {
                    return true;
                }

                $offset = 0;

                while (($position = strpos($value, '?', $offset)) !== false) {
                    if (isset($lookup[substr($value, 0, $position)])) {
                        return true;
                    }

                    $offset = $position + 1;
                }

                return false;
            })
            ->keys()
            ->all();

        foreach ($keys as $key) {
            $this->cache->forget($this->normalizeKey('responses:'.$key));
        }

        $this->forgetUrls($keys, $domain);

        foreach ($paths as $path) {
            UrlInvalidated::dispatch($path, $domain);
        }
    }
}
