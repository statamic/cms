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
        setlocale(LC_TIME, $site->locale());

        // The short locale is used for your translations. (eg. if you set your site's locale
        // to "fr_FR", the translator will look for "fr" files rather than "fr_FR" files.)
        app()->setLocale($site->shortLocale());

        Carbon::setToStringFormat(Statamic::dateFormat());

        return $next($request);
    }
}
