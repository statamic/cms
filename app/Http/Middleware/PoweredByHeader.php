<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class PoweredByHeader
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
        $response = $next($request);

        if (config('statamic.system.send_powered_by_header') && $response instanceof Response) {
            $response->header('X-Powered-By', 'Statamic');
        }

        return $response;
    }
}
