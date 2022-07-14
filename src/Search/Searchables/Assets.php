<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Statamic\Assets\AssetCollection;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Facades\Asset;

class Assets extends Provider
{
    public function referencePrefix(): string
    {
        return 'asset';
    }

    public function provide(): Collection
    {
        if ($this->usesWildcard()) {
            return Asset::all();
        }

        return AssetCollection::make($this->keys)
            ->flatMap(fn ($key) => Asset::whereContainer($key));
    }

    public function contains($searchable): bool
    {
        if (! $searchable instanceof AssetContract) {
            return false;
        }

        return $this->usesWildcard() || in_array($searchable->containerHandle(), $this->keys);
    }

    public function isSearchable($searchable): bool
    {
        return $searchable instanceof AssetContract;
    }

    public function find(array $keys): Collection
    {
        return collect($keys)->map(function ($id) {
            [$container, $path] = explode('::', $id);

            return compact('container', 'path');
        })
        ->groupBy->container
        ->flatMap(fn ($group, $container) => Asset::query()
            ->where('container', $container)
            ->whereIn('path', $group->map->path->all())
            ->get()
        );
    }
}
