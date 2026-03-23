<?php

namespace Statamic\Http\Middleware;

use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Closure;
use Illuminate\Support\Facades\Date;
use ReflectionClass;
use Statamic\Facades\Site;
use Statamic\Statamic;
use Statamic\Support\Arr;

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

        // Use the site's lang for translations.
        $originalTranslatorLocale = app('translator')->getLocale();
        app('translator')->setLocale($site->lang());

        // Get original Carbon format so it can be restored later.
        $originalToStringFormat = $this->getToStringFormat();
        Date::setToStringFormat(function (CarbonInterface|CarbonInterval $date) {
            if ($date instanceof CarbonInterval) {
                return $date->forHumans();
            }

            return $date->setTimezone(Statamic::displayTimezone())->format(Statamic::dateFormat());
        });

        $response = $next($request);

        // Reset everything back to their originals. This allows everything
        // not within the scope of the request to be the "defaults".
        setlocale(LC_TIME, $originalLocale);
        app('translator')->setLocale($originalTranslatorLocale);
        Date::setToStringFormat($originalToStringFormat);

        return $response;
    }

    /**
     * This method is used to get the current toStringFormat for Carbon, in order for us
     * to restore it later. There's no getter for it, so we need to use reflection.
     *
     * @throws \ReflectionException
     */
    private function getToStringFormat(): string|\Closure|null
    {
        $reflection = new ReflectionClass($date = Date::now());

        $factory = $reflection->getMethod('getFactory');

        return Arr::get($factory->invoke($date)->getSettings(), 'toStringFormat');
    }
}
