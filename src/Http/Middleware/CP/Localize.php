<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\Preference;
use Statamic\Facades\Site;

class Localize
{
    public function handle($request, Closure $next)
    {
        $locale = Preference::get('locale') ?? app()->getLocale();

        if (! Preference::get('locale')) {
            $locale = Site::selected()->locale();
        }

        // Make locale config with dashes backwards compatible, as they should be underscores.
        $locale = str_replace('-', '_', $locale);

        if (str_contains($locale, '_')) {
            $locale = strstr($locale, '_', true);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
