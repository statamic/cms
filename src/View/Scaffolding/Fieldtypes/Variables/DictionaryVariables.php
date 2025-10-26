<?php

namespace Statamic\View\Scaffolding\Fieldtypes\Variables;

class DictionaryVariables
{
    private static array $defaults = ['label', 'value'];

    protected array $dictionaryVariables = [];

    public function register(string $name, array $variables): static
    {
        $this->dictionaryVariables[$name] = $variables;

        return $this;
    }

    public function resolve(string $dictionaryType): array
    {
        return collect($this->dictionaryVariables[$dictionaryType] ?? [])
            ->merge(self::$defaults)
            ->unique()
            ->values()
            ->all();
    }
}
