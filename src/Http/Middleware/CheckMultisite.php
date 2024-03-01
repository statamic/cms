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
        if (Statamic::pro() || $request->is('_ignition*')) {
            return $next($request);
        }

        $sites = Site::all();

        throw_if($sites->count() > 1, new StatamicProRequiredException('Statamic Pro is required to use multiple sites.'));

        return $next($request);
    }
}
