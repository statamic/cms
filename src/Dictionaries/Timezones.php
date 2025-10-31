<?php

namespace Statamic\Dictionaries;

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
        return collect(timezone_abbreviations_list())
            ->flatMap(fn (array $timezones, string $abbreviation) => collect($timezones)
                ->map(fn (array $data) => $this->tzData($data, $abbreviation))
            )->all();
    }

    private function tzData(array $data, string $abbreviation): array
    {
        return [
            'abbreviation' => $abbreviation,
            'name' => $data['timezone_id'],
            'offset' => $this->getOffset($data['offset']),
        ];
    }

    private function getOffset(int $offsetInSecs): string
    {
        $hoursAndSec = gmdate('H:i', abs($offsetInSecs));

        return stripos($offsetInSecs, '-') === false ? "+{$hoursAndSec}" : "-{$hoursAndSec}";
    }
}
