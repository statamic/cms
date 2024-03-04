<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades\User;

class CanManageBlueprints
{
    public function handle($request, Closure $next)
    {
        $user = User::current();

        if ($user->cant('configure fields') && $user->cant('configure form fields')) {
            throw new AuthorizationException('Unauthorized.');
        }

        return $next($request);
    }
}
