<?php

namespace Statamic\Dictionaries;

class Timezones extends Dictionary
{
    public function options(?string $search = null): array
    {
        return collect(timezone_identifiers_list())
            ->filter(fn ($timezone) => $search ? str_contains(strtolower($timezone), strtolower($search)) : true)
            ->mapWithKeys(fn ($timezone) => [$timezone => $timezone])
            ->all();
    }

    public function get(string $key): string|array
    {
        return $key;
    }
}
