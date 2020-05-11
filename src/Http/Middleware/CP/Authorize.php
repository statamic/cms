<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Exceptions\AuthenticationException;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades\User;

class Authorize
{
    public function handle($request, Closure $next)
    {
        $user = User::current();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        if ($user->cant('access cp')) {
            // dd('theres a user but they are unauthorized', $user);
            throw new AuthorizationException('Unauthorized.');
        }

        return $next($request);
    }
}
