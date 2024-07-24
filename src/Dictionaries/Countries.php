<?php

namespace Statamic\Dictionaries;

use Illuminate\Support\Collection;
use Statamic\Facades\File;

class Countries extends Dictionary
{
    public function options(?string $search = null): array
    {
        return $this->getCountries()
            ->when($this->context['region'] ?? false, function ($collection) {
                return $collection->where('region', $this->context['region']);
            })
            ->when($search ?? false, function ($collection) use ($search) {
                return $collection->filter(function (array $country) use ($search) {
                    return str_contains(strtolower($country['name']), strtolower($search))
                        || str_contains(strtolower($country['iso3']), strtolower($search));
                });
            })
            ->mapWithKeys(function (array $country) {
                return [$country['iso3'] => "{$country['emoji']} {$country['name']}"];
            })
            ->all();
    }

    public function get(string $key): array
    {
        return $this->getCountries()->firstWhere('iso3', $key);
    }

    protected function fieldItems()
    {
        return [
            'region' => [
                'display' => __('Region'),
                'instructions' => __('statamic::messages.dictionaries_countries_region_instructions'),
                'type' => 'select',
                'options' => $this->getCountries()->unique('region')->pluck('region', 'region')->filter()->all(),
            ],
        ];
    }

    private function getCountries(): Collection
    {
        return collect(json_decode(File::get(__DIR__.'/../../resources/dictionaries/countries.json'), true));
    }
}
