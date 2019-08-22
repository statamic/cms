<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\API\User;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (User::current()) {
            return redirect(cp_route('index'));
        }

        return $next($request);
    }
}
