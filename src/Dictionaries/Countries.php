<?php

namespace Statamic\Dictionaries;

use Illuminate\Support\Collection;
use Statamic\Facades\File;

class Countries extends Dictionary
{
    /**
     * Returns all options.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->getCountries()->mapWithKeys(function ($country) {
            return [$country['iso3'] => "{$country['emoji']} {$country['name']}"];
        })->all();
    }

    /**
     * Returns data for a single option, given the option's key.
     *
     * @param string $option
     * @return array
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
