<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Support\Collection as SupportCollection;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Query\Builder;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;

trait QueriesAuthorEntries
{
    protected function queryAuthorEntries(Builder $query, Collection $collection): void
    {
        $blueprintsWithAuthor = $this->blueprintsWithAuthor($collection->entryBlueprints());
        $blueprintsWithoutAuthor = $this->blueprintsWithoutAuthor($collection->entryBlueprints());

        $query->where(fn ($query) => $query
            ->whereNotIn('collectionHandle', [$collection->handle()]) // Needed for entries fieldtypes configured for multiple collections
            ->when($blueprintsWithAuthor, fn ($query) => $query
                ->orWhere(fn ($query) => $query
                    ->whereIn('blueprint', $blueprintsWithAuthor)
                    ->whereHas('author', fn ($subquery) => $subquery->where('id', User::current()->id()))
                )
            )
            ->orWhereIn('blueprint', $blueprintsWithoutAuthor)
        );
    }

    protected function blueprintsWithAuthor(SupportCollection $blueprints): array
    {
        return $blueprints
            ->filter(fn (Blueprint $blueprint) => $blueprint->hasField('author'))
            ->map->handle()->all();
    }

    protected function blueprintsWithoutAuthor(SupportCollection $blueprints): array
    {
        return $blueprints
            ->filter(fn (Blueprint $blueprint) => ! $blueprint->hasField('author'))
            ->map->handle()->all();
    }
}
