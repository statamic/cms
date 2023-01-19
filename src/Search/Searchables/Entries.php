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
        $entries = $this->usesWildcard()
            ? Entry::all()
            : Entry::query()->whereIn('collection', $this->keys)->get();

        return $entries->filter($this->filter());
    }

    public function contains($searchable): bool
    {
        if (! $searchable instanceof EntryContract) {
            return false;
        }

        if (! $this->usesWildcard() && ! in_array($searchable->collectionHandle(), $this->keys)) {
            return false;
        }

        return $this->filter()($searchable);
    }

    public function find(array $ids): Collection
    {
        return Entry::query()->whereIn('id', $ids)->get();
    }

    protected function defaultFilter()
    {
        return fn ($item) => $item->published();
    }
}
