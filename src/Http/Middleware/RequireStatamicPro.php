<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Exceptions\StatamicProAuthorizationException;
use Statamic\Statamic;

class RequireStatamicPro
{
    public function handle($request, Closure $next)
    {
        if (! Statamic::pro()) {
            throw new StatamicProAuthorizationException(__('Statamic Pro is required.'));
        }

        return $next($request);
    }
}
