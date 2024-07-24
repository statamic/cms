<?php

namespace Statamic\Dictionaries;

use DateTimeZone;
use Illuminate\Support\Carbon;

class Timezones extends Dictionary
{
    public function options(?string $search = null): array
    {
        $searchingOffset = $this->isSearchingOffset($search);

        return collect(timezone_identifiers_list())
            ->map(function ($tz) {
                return [
                    'tz' => $tz,
                    'offset' => $this->getOffset($tz),
                ];
            })
            ->when($search, function ($collection) use ($search, $searchingOffset) {
                return $collection->filter(function ($timezone) use ($search, $searchingOffset) {
                    return str_contains(strtolower($timezone['tz']), strtolower($search))
                        || ($searchingOffset && str_contains($timezone['offset'], $search));
                });
            })
            ->mapWithKeys(fn ($tz) => [$tz['tz'] => $tz['tz'].' ('.$tz['offset'].')'])
            ->all();
    }

    private function isSearchingOffset($query): bool
    {
        return is_numeric($query) || preg_match('/^[+-]\d+$/', $query);
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
