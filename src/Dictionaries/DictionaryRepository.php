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

    public function find(string $dictionary): ?Dictionary
    {
        if ($class = app('statamic.dictionaries')->get($dictionary)) {
            return app($class);
        }
    }
}
