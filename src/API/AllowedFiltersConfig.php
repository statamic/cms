<?php

namespace Statamic\API;

use Statamic\Facades\Collection;
use Statamic\Support\Arr;

class AllowedFiltersConfig
{
    /**
     * Get allowed filters for collection entries query.
     *
     * @param  string  $configFile
     * @param  string|array  $collectionHandles
     * @return array
     */
    public function allowedForCollectionEntries($configFile, $collectionHandles)
    {
        $config = config("statamic.{$configFile}.resources.collections", false);

        // If collections config is just a basic `true`, then no filters should be allowed by default.
        if ($config === true) {
            return [];
        }

        // Determine which collections are being queried.
        $collections = collect($collectionHandles === '*' ? Collection::handles() : $collectionHandles);

        // Determine if any of our queried collections are explicitly disabled.
        $disabled = $collections
            ->filter(fn ($collection) => Arr::get($config, "{$collection}.allowed_filters") === false)
            ->isNotEmpty();

        // If any queried collection is explicitly disabled, then no filters should be allowed.
        if ($disabled) {
            return [];
        }

        // Determine `allowed_filters` by filtering out any that don't appear in all of them.
        // And a collection named `*` will apply to all collections.
        return $collections
            ->map(fn ($collection) => $config[$collection]['allowed_filters'] ?? [])
            ->reduce(function ($carry, $allowedFilters) use ($config) {
                return $carry->intersect($allowedFilters)->merge($config['*']['allowed_filters'] ?? []);
            }, collect($config[$collections[0]]['allowed_filters'] ?? []))
            ->all();
    }
}
