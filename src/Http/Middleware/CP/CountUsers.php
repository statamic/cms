<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Exceptions\StatamicProRequiredException;
use Statamic\Facades\User;
use Statamic\Statamic;

class CountUsers
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (! Statamic::pro() && User::count() > 1) {
            throw new StatamicProRequiredException('Statamic Pro is required for multiple users.');
        }

        return $next($request);
    }
}
