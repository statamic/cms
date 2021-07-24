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
        if ($this->cacher->canBeCached($request) && $this->cacher->hasCachedPage($request)) {
            return $this->cacher->response($request, response(null));
        }

        $response = $next($request);

        return $this->cacher->response($request, $response);
    }
}
