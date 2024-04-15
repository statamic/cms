<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;

class RedirectIfAuthorized
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (! Auth::guard($guard)->check()) {
            return $next($request);
        }

        $user = User::current();

        $url = $user->can('access cp') ? cp_route('index') : '/';

        return redirect($url)->withError(__("You can't do this while logged in"));
    }
}
