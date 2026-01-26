<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Query\Builder;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;

trait QueriesAuthorEntries
{
    protected function queryAuthorEntries(Builder $query, Collection $collection): void
    {
        $blueprintsWithAuthor = $this->blueprintsWithAuthor($collection);

        if (empty($blueprintsWithAuthor)) {
            return;
        }

        $query->where(fn ($query) => $query
            // Exclude entries from other collections (for entries fieldtypes with multiple collections)
            ->whereNotIn('collectionHandle', [$collection->handle()])
            // Include entries with blueprints where the current user is the author
            ->orWhere(fn ($query) => $query
                ->whereIn('blueprint', $blueprintsWithAuthor)
                ->whereHas('author', fn ($query) => $query->where('id', User::current()->id()))
            )
            // Include entries with blueprints that don't have an author
            ->orWhereIn('blueprint', $this->blueprintsWithoutAuthor($collection))
        );
    }

    protected function blueprintsWithAuthor(Collection $collection): array
    {
        return $collection->entryBlueprints()
            ->filter(fn (Blueprint $blueprint) => $blueprint->hasField('author'))
            ->map->handle()->all();
    }

    protected function blueprintsWithoutAuthor(Collection $collection): array
    {
        return $collection->entryBlueprints()
            ->filter(fn (Blueprint $blueprint) => ! $blueprint->hasField('author'))
            ->map->handle()->all();
    }
}
