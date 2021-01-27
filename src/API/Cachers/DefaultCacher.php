<?php

namespace Statamic\API\Cachers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Statamic\API\AbstractCacher;

class DefaultCacher extends AbstractCacher
{
    /**
     * Remember cache by endpoint.
     *
     * @param Request $request
     * @param Closure $callback
     */
    public function remember(Request $request, Closure $callback)
    {
        return Cache::remember($this->cacheKey($request), $this->cacheExpiry(), $callback);
    }

    /**
     * Get cache key for endpoint.
     *
     * @param Request $request
     * @return string
     */
    public function cacheKey(Request $request)
    {
        $domain = $request->root();
        $fullUrl = $request->fullUrl();

        $key = str_replace($domain, '', $fullUrl);

        return $this->normalizeKey($key);
    }
}
