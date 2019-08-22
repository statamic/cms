<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\API\User;
use Statamic\Exceptions\AuthenticationException;

class Authenticate
{
    public function handle($request, Closure $next)
    {
        $user = User::current();

        if (! $user || $user->cant('access cp')) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return $next($request);
    }
}
