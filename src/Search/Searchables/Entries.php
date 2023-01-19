<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;

class Entries extends Provider
{
    public function referencePrefix(): string
    {
        return 'entry';
    }

    public function provide(): Collection
    {
        if ($this->usesWildcard()) {
            return Entry::all();
        }

        return Entry::query()->whereIn('collection', $this->keys)->get();
    }

    public function contains($searchable): bool
    {
        if (! $searchable instanceof EntryContract) {
            return false;
        }

        return $this->usesWildcard() || in_array($searchable->collectionHandle(), $this->keys);
    }

    public function isSearchable($searchable): bool
    {
        return $searchable instanceof EntryContract;
    }

    public function find(array $ids): Collection
    {
        return Entry::query()->whereIn('id', $ids)->get();
    }
}
