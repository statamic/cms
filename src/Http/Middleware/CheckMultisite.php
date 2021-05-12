<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Exceptions\StatamicProRequiredException;
use Statamic\Statamic;

class CheckMultisite
{
    public function handle($request, Closure $next)
    {
        if (Statamic::pro() || $request->is('_ignition*')) {
            return $next($request);
        }

        $sites = config('statamic.sites.sites');

        throw_if(count($sites) > 1, new StatamicProRequiredException('Statamic Pro is required to use multiple sites.'));

        return $next($request);
    }
}
