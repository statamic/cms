<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\User;

class Configurable
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
        if (! User::current()->isSuper()) {
            abort(403);
        }

        return $next($request);
    }
}
