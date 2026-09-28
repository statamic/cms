<?php

namespace Statamic\Http\Middleware\CP;

use Carbon\CarbonInterface;
use Carbon\CarbonInterval;
use Closure;
use DateTime;
use Illuminate\Support\Facades\Date;
use ReflectionClass;
use Statamic\Facades\Preference;
use Statamic\Statamic;
use Statamic\Support\Arr;

class Localize
{
    public function handle($request, Closure $next)
    {
        $locale = Preference::get('locale') ?? app()->getLocale();

        // Make locale config with dashes backwards compatible, as they should be underscores.
        $locale = str_replace('-', '_', $locale);

        app()->setLocale($locale);

        // Get original Carbon format so it can be restored later.
        $originalToStringFormat = $this->getToStringFormat();
        Date::setToStringFormat(function (CarbonInterface|CarbonInterval $date) {
            if ($date instanceof CarbonInterval) {
                return $date->forHumans();
            }

            return $date->setTimezone(Statamic::displayTimezone())->format(Statamic::dateFormat());
        });

        Date::setToStringFormat(DateTime::ATOM);

        try {
            $response = $next($request);
        } finally {
            Date::setToStringFormat($originalToStringFormat);
        }

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
