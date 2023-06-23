<?php

namespace Statamic\API\Middleware;

use Closure;
use Statamic\API\Cacher;
use Statamic\Exceptions\NotFoundHttpException;

class Cache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->statamicToken()) {
            return $next($request);
        }

        $cacher = app(Cacher::class);

        if ($response = $cacher->get($request)) {
            return $response;
        }

        $response = $next($request);

        if ($this->shouldBeCached($response)) {
            $cacher->put($request, $this->cleanResponse($response));
        }

        return $response;
    }

    private function shouldBeCached($response)
    {
        return $response->isOk() || $response->isNotFound();
    }

    private function cleanResponse($response)
    {
        if ($response->exception instanceof NotFoundHttpException) {
            $response->exception = null;
        }

        return $response;
    }
}
