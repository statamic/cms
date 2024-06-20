<?php

namespace Statamic\Dictionaries;

use Illuminate\Support\Collection;
use Statamic\Facades\File;

class Countries extends Dictionary
{
    /**
     * Returns all options.
     */
    public function options(?string $search = null): array
    {
        return $this->getCountries()
            ->when($search ?? false, function ($collection) use ($search) {
                return $collection->filter(function (array $country) use ($search) {
                    return str_contains(strtolower($country['name']), strtolower($search));
                });
            })
            ->mapWithKeys(function (array $country) {
                return [$country['iso3'] => "{$country['emoji']} {$country['name']}"];
            })
            ->all();
    }

    /**
     * Returns data for a single option, given the option's key.
     */
    public function get(string $option): array
    {
        return $this->getCountries()->filter(fn ($value, $key) => $value['iso3'] === $option)->first();
    }

    private function getCountries(): Collection
    {
        return collect(json_decode(File::get(__DIR__.'/../../resources/dictionaries/countries.json'), true));
    }
}
