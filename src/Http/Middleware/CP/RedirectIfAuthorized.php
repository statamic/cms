<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\CP\Toast;
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
        if ($user = User::current()) {
            if ($user->can('access cp')) {
                Toast::error(__("You can't do this while logged in"));

                return redirect(cp_route('index'));
            }

            return redirect('/');
        }

        return $next($request);
    }
}
