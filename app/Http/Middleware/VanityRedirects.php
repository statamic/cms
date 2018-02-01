<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\API\Str;

class VanityRedirects
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
        $routes = config('statamic.routes.vanity', []);

        $url = Str::ensureLeft($request->path(), '/');

        if (array_key_exists($url, $routes)) {
            return redirect($routes[$url]);
        }

        return $next($request);
    }
}
