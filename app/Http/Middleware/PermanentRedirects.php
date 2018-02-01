<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\API\Str;

class PermanentRedirects
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
        $routes = config('statamic.routes.redirect', []);

        $url = Str::ensureLeft($request->path(), '/');

        if (array_key_exists($url, $routes)) {
            return redirect($routes[$url], 301);
        }

        return $next($request);
    }
}
