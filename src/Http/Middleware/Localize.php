<?php

namespace Statamic\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Statamic\Facades\Site;
use Statamic\Statamic;

class Localize
{
    public function handle($request, Closure $next)
    {
        $site = Site::current();

        // Dates, Carbon, etc expect the full locale. (eg. "fr_FR" or whatever is
        // installed on your actual server. You can check by running `locale -a`).
        // We'll save the original locale so we can reset it later. Of course,
        // you can get the locale by calling the setlocale method. Logical.
        $originalLocale = setlocale(LC_TIME, 0);
        setlocale(LC_TIME, $site->locale());

        // The sites lang is used for your translations. (eg. if you set your site's lang
        // to "fr_FR", the translator will look for "fr_FR" files rather than "fr" files
        // but if not set the translator will look for "fr" files rather than "fr_FR"
        // files.) Again, we'll save the original locale so we can reset it later.
        $originalAppLocale = app()->getLocale();
        app()->setLocale($site->lang());

        // Get original Carbon format so it can be restored later.
        // There's no getter for it, so we'll use reflection.
        $format = (new \ReflectionClass(Carbon::class))->getProperty('toStringFormat');
        $format->setAccessible(true);
        $originalToStringFormat = $format->getValue();
        Carbon::setToStringFormat(Statamic::dateFormat());

        $response = $next($request);

        // Reset everything back to their originals. This allows everything
        // not within the scope of the request to be the "defaults".
        setlocale(LC_TIME, $originalLocale);
        app()->setLocale($originalAppLocale);
        Carbon::setToStringFormat($originalToStringFormat);

        return $response;
    }
}
