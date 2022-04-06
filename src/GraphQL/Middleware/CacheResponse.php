<?php

namespace Statamic\GraphQL\Middleware;

use Closure;
use Statamic\Contracts\GraphQL\ResponseCache;

class CacheResponse
{
    public function handle($request, Closure $next)
    {
        if ($request->statamicToken()) {
            return $next($request);
        }

        $cache = app(ResponseCache::class);

        if ($response = $cache->get($request)) {
            return $response;
        }

        $response = $next($request);

        $cache->put($request, $response);

        return $response;
    }
}
