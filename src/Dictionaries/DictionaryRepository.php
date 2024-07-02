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

        $dictionary = app($dictionary);

        if (! $dictionary) {
            return null;
        }

        if (! method_exists($dictionary, 'context')) {
            return $dictionary;
        }

        return $dictionary->context($context);
    }
}
