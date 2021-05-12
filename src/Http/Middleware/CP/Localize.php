<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\User;

class Localize
{
    public function handle($request, Closure $next)
    {
        $locale = User::current()->getPreference('locale') ?? app()->getLocale();

        // Make locale config with dashes backwards compatible, as they should be underscores.
        $locale = str_replace('-', '_', $locale);

        app()->setLocale($locale);

        return $next($request);
    }
}
