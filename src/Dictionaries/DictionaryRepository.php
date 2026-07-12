<?php

namespace Statamic\Dictionaries;

use Illuminate\Support\Collection;

class DictionaryRepository
{
    public function all(): Collection
    {
        return app('statamic.dictionaries')
            ->map(fn ($class) => app($class))
            ->filter()
            ->values();
    }

    public function find(string $handle, array $context = []): ?Dictionary
    {
        if (! $dictionary = app('statamic.dictionaries')->get($handle)) {
            return null;
        }

        /** @var $dictionary Dictionary */
        if (! $dictionary = app($dictionary)) {
            return null;
        }

        return $dictionary->setConfig($context);
    }
}
