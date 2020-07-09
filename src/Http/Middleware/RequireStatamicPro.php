<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Statamic;

class RequireStatamicPro
{
    public function handle($request, Closure $next)
    {
        if (! Statamic::pro()) {
            throw new AuthorizationException(__('Statamic Pro is required.'));
        }

        return $next($request);
    }
}
