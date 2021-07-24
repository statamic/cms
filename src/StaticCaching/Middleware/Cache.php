<?php

namespace Statamic\StaticCaching\Middleware;

use Closure;
use Statamic\Statamic;
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
        if ($this->canBeCached($request) && $this->cacher->canBeCached($request) && $this->cacher->hasCachedPage($request)) {
            $response = response(null); // Pass empty response to Cacher, Cacher will add the content.

            return $this->cacher->response($request, $response);
        }

        $response = $next($request);

        return $this->cacher->response($request, $response);
    }

    private function canBeCached($request)
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        if (Statamic::isCpRoute()) {
            return false;
        }

        return true;
    }
}
