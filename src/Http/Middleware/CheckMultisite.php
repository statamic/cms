<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Exceptions\StatamicProRequiredException;
use Statamic\Facades\Site;
use Statamic\Statamic;

class CheckMultisite
{
    public function handle($request, Closure $next)
    {
        if (Statamic::pro() || ! Site::multiEnabled() || $request->is('_ignition*')) {
            return $next($request);
        }

        throw new StatamicProRequiredException('Statamic Pro is required to use multiple sites.');

        return $next($request);
    }
}
