<?php

namespace Statamic\StaticCaching\Middleware;

use Closure;
use Statamic\StaticCaching\Cacher;

class Cache
{
    /**
     * @var Cacher
     */
    private $cacher;

    public function __construct(Cacher $cacher)
    {
        $this->cacher = $cacher;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Perform any final actions for the request lifecycle.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {
        // Only GET requests should be cached. For instance, Live Preview hits frontend URLs as
        // POST requests to preview the changes. We don't want those to trigger any caching,
        // or else pending changes will be shown immediately, even without hitting save.
        if ($request->method() !== 'GET') {
            return;
        }

        // Draft pages should not be cached.
        if ($response->headers->has('X-Statamic-Draft')) {
            return;
        }

        if ($response->getStatusCode() !== 200 || $response->getContent() == '') {
            return;
        }

        $this->cacher->cachePage($request, $response);
    }
}
