<?php

namespace Statamic\Dictionaries;

use DateTimeZone;
use Illuminate\Support\Carbon;

class Timezones extends Dictionary
{
    public function options(?string $search = null): array
    {
        return collect(timezone_identifiers_list())
            ->filter(fn ($timezone) => $search ? str_contains(strtolower($timezone), strtolower($search)) : true)
            ->mapWithKeys(fn ($timezone) => [$timezone => $timezone])
            ->all();
    }

    public function get(string $key): array
    {
        return [
            'name' => $key,
            'offset' => $this->getOffset($key),
        ];
    }

    private function getOffset(string $tz): string
    {
        $tz = new DateTimeZone($tz);
        $utcTime = Carbon::now('UTC');
        $offsetInSecs = $tz->getOffset($utcTime);
        $hoursAndSec = gmdate('H:i', abs($offsetInSecs));

        return stripos($offsetInSecs, '-') === false ? "+{$hoursAndSec}" : "-{$hoursAndSec}";
    }
}
