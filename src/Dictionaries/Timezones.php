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
            ->map(fn ($tz) => $this->get($tz))
            ->when($search, function ($collection) use ($search, $searchingOffset) {
                return $collection->filter(function ($tz) use ($search, $searchingOffset) {
                    return str_contains(strtolower($tz['name']), strtolower($search))
                        || ($searchingOffset && str_contains($tz['offset'], $search));
                });
            })
            ->mapWithKeys(fn ($tz) => [$tz['name'] => $tz['name'].' ('.$tz['offset'].')'])
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
