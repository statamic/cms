<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
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

    public function provide(): Collection|LazyCollection
    {
        $query = Entry::query();

        if (! $this->usesWildcard()) {
            $query->whereIn('collection', $this->keys);
        }

        if ($site = $this->index->locale()) {
            $query->where('site', $site);
        }

        $this->applyQueryScope($query);

        if ($filter = $this->filter()) {
            return $query
                ->lazy(config('statamic.search.chunk_size'))
                ->filter($filter)
                ->values()
                ->map->reference();
        }

        $query->whereStatus('published');

        return $query->pluck('reference');
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

        if ($filter = $this->filter()) {
            return $filter($searchable);
        }

        $query = Entry::query()
            ->whereStatus('published')
            ->where('id', $searchable->id());

        $this->applyQueryScope($query);

        return $query->exists();
    }

    public function find(array $ids): Collection
    {
        return Entry::query()->whereIn('id', $ids)->get();
    }
}
