<?php

namespace Statamic\StaticCaching;

use Statamic\API\Config;
use Illuminate\Http\Request;
use Statamic\API\Str;
use Statamic\API\URL;

class ApplicationCacher extends AbstractCacher
{
    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Cache a page
     *
     * @param \Illuminate\Http\Request $request     Request associated with the page to be cached
     * @param string                   $content     The response content to be cached
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
        $this->cacheUrl($key, $url);

        $key = $this->normalizeKey('responses:'.$key);
        $value = $this->normalizeContent($content);

        if ($expiration = $this->getDefaultExpiration()) {
            $this->cache->put($key, $value, $expiration);
        } else {
            $this->cache->forever($key, $value);
        }
    }

    /**
     * Get a cached page
     *
     * @param Request $request
     * @return string
     */
    public function getCachedPage(Request $request)
    {
        $url = $this->getUrl($request);

        $key = $this->makeHash($url);

        return $this->cache->get($this->normalizeKey('responses:'.$key));
    }

    /**
     * Flush out the entire static cache
     *
     * @return void
     */
    public function flush()
    {
        $this->getUrls()->keys()->each(function ($key) {
            $this->cache->forget($this->normalizeKey('responses:'.$key));
        });

        $this->flushUrls();
    }

    /**
     * Invalidate a URL
     *
     * @param string $url
     * @return void
     */
    public function invalidateUrl($url)
    {
        if (! $key = $this->getUrls()->flip()->get($url)) {
            // URL doesn't exist, nothing to invalidate.
            return;
        }

        $this->cache->forget($this->normalizeKey('responses:'.$key));

        $this->forgetUrl($key);
    }
}
