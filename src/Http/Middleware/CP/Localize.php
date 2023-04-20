<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\Preference;

class Localize
{
    public function handle($request, Closure $next)
    {
        $locale = Preference::get('locale') ?? app()->getLocale();

        // Make locale config with dashes backwards compatible, as they should be underscores.
        $locale = str_replace('-', '_', $locale);

        app()->setLocale($locale);

        return $next($request);
    }
}
