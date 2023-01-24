<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;

class Entries extends Provider
{
    public static function handle(): string
    {
        return 'collection';
    }

    public static function referencePrefix(): string
    {
        return 'entry';
    }

    public function provide(): Collection
    {
        $query = Entry::query();

        if (! $this->usesWildcard()) {
            $query->whereIn('collection', $this->keys);
        }

        if ($site = $this->index->locale()) {
            $query->where('site', $site);
        }

        return $query->get()->filter($this->filter())->values();
    }

    public function contains($searchable): bool
    {
        if (! $searchable instanceof EntryContract) {
            return false;
        }

        if (! $this->usesWildcard() && ! in_array($searchable->collectionHandle(), $this->keys)) {
            return false;
        }

        if (($site = $this->index->locale()) && $site !== $searchable->locale()) {
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
