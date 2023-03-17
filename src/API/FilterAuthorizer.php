<?php

namespace Statamic\API;

use Statamic\Facades;
use Statamic\Support\Arr;

class FilterAuthorizer
{
    /**
     * Get allowed filters for resource.
     *
     * For example, which filters are allowed when querying against the `users` resource?
     *
     * @param  string  $configFile
     * @param  string  $queriedResource
     * @return array
     */
    public function allowedForResource($configFile, $queriedResource)
    {
        $config = config("statamic.{$configFile}.resources.{$queriedResource}.allowed_filters");

        // Use explicitly configured `allowed_filters` array, otherwise no filters should be allowed.
        return is_array($config)
            ? $config
            : [];
    }

    /**
     * Get allowed filters for sub-resource(s).
     *
     * For example, which filters are allowed when querying against `pages` and `articles` entries within the `collections` resource?
     *
     * @param  string  $configFile
     * @param  string  $queriedResource
     * @param  string|array  $queriedHandles
     * @return array
     */
    public function allowedForSubResources($configFile, $queriedResource, $queriedHandles)
    {
        $config = config("statamic.{$configFile}.resources.{$queriedResource}", false);

        // If resource config is just a basic `true`, then no filters should be allowed by default.
        if ($config === true) {
            return [];
        }

        // Determine which resources are being queried.
        $resources = collect($queriedHandles === '*' ? $this->getAllHandlesForResource($queriedResource) : $queriedHandles);

        // Determine if any of our queried resources are explicitly disabled.
        $disabled = $resources
            ->filter(fn ($resource) => Arr::get($config, "{$resource}.allowed_filters") === false)
            ->isNotEmpty();

        // If any queried resource is explicitly disabled, then no filters should be allowed.
        if ($disabled) {
            return [];
        }

        // Determine `allowed_filters` by filtering out any that don't appear in all of them.
        // And a resource named `*` will apply to all resources at once.
        return $resources
            ->map(fn ($resource) => $config[$resource]['allowed_filters'] ?? [])
            ->reduce(function ($carry, $allowedFilters) use ($config) {
                return $carry->intersect($allowedFilters)->merge($config['*']['allowed_filters'] ?? []);
            }, collect($config[$resources[0]]['allowed_filters'] ?? []))
            ->all();
    }

    /**
     * Get all possible handles for resource (for when evaluating `*` config).
     *
     * @param  string  $resource
     * @return array
     */
    protected function getAllHandlesForResource($resource)
    {
        if ($resource === 'collections') {
            return Facades\Collection::handles()->all();
        }

        return [];
    }
}
