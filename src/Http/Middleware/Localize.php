<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Facades\Site;

class Localize
{
    public function handle($request, Closure $next)
    {
        $locale = Site::current()->shortLocale();

        app()->setLocale($locale);

        return $next($request);
    }
}
