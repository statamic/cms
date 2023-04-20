<?php

namespace Statamic\Search\Searchables;

use Illuminate\Support\Collection;
use Statamic\Assets\AssetCollection;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Facades\Asset;

class Assets extends Provider
{
    public static function handle(): string
    {
        return 'assets';
    }

    public static function referencePrefix(): string
    {
        return 'asset';
    }

    public function provide(): Collection
    {
        $assets = $this->usesWildcard()
            ? Asset::all()
            : AssetCollection::make($this->keys)
                ->flatMap(fn ($key) => Asset::whereContainer($key));

        return $assets->filter($this->filter())->values();
    }

    public function contains($searchable): bool
    {
        if (! $searchable instanceof AssetContract) {
            return false;
        }

        if (! $this->usesWildcard() && ! in_array($searchable->containerHandle(), $this->keys)) {
            return false;
        }

        return $this->filter()($searchable);
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
