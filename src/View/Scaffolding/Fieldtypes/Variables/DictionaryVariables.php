<?php

namespace Statamic\View\Scaffolding\Fieldtypes\Variables;

class DictionaryVariables
{
    private static array $defaults = ['label', 'value'];

    protected array $dictionaryVariables = [
        'countries' => ['name', 'iso3', 'iso2', 'region', 'subregion', 'emoji'],
        'currencies' => ['code', 'name', 'symbol', 'decimals'],
        'languages' => ['code', 'name'],
        'locales' => ['name'],
        'timezones' => ['name', 'offset'],
    ];

    public function resolve(string $dictionaryType): array
    {
        return collect($this->dictionaryVariables[$dictionaryType] ?? [])
            ->merge(self::$defaults)
            ->unique()
            ->values()
            ->all();
    }
}
