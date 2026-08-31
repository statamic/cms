<?php

namespace Statamic\View\Scaffolding\Fieldtypes\Variables;

use Statamic\Facades\Dictionary;

class DictionaryVariables
{
    public function resolve(string $dictionaryType): array
    {
        $dictionary = Dictionary::find($dictionaryType);

        $firstOption = collect($dictionary->options())->keys()->first();

        return collect($dictionary->get($firstOption)->toArray())
            ->except(['key', 'value'])
            ->keys()
            ->push('value')
            ->all();
    }
}
