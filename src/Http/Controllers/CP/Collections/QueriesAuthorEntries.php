<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Query\Builder;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint;
use Statamic\Support\FileCollection;

trait QueriesAuthorEntries
{
    protected function queryAuthorEntries(Builder $query, Collection $collection): void
    {
        $query
            ->where(fn ($query) => $query
                ->whereNotIn('collection', [$collection->handle()]) // Needed for entries fieldtypes configured for multiple collections
                ->orWhere(fn ($query) => $query
                    ->whereIn('blueprint', $this->blueprintsWithAuthor($collection->entryBlueprints()))
                    ->whereIn('author', [User::current()->id()])
                    ->orWhereJsonContains('author', User::current()->id())
                )
                ->orWhereIn('blueprint', $this->blueprintsWithoutAuthor($collection->entryBlueprints()))
            );
    }

    protected function blueprintsWithAuthor(FileCollection $blueprints): array
    {
        return $blueprints
            ->filter(fn (Blueprint $blueprint) => $blueprint->hasField('author'))
            ->map->handle()->all();
    }

    protected function blueprintsWithoutAuthor(FileCollection $blueprints): array
    {
        return $blueprints
            ->filter(fn (Blueprint $blueprint) => ! $blueprint->hasField('author'))
            ->map->handle()->all();
    }
}
