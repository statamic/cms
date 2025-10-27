<?php

namespace Statamic\Dictionaries;

use DateTimeZone;
use Illuminate\Support\Carbon;

class Timezones extends BasicDictionary
{
    protected string $valueKey = 'name';
    protected array $keywords = ['timezone', 'tz', 'zone', 'time', 'date'];

    protected function getItemLabel(array $item): string
    {
        return $item['name'].' ('.$item['offset'].')';
    }

    protected function getItems(): array
    {
        return collect(DateTimeZone::listIdentifiers())
            ->map(fn ($tz) => ['name' => $tz, 'offset' => $this->getOffset($tz)])
            ->all();
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
