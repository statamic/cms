<?php

namespace Statamic\API;

use Statamic\Facades\Collection;

class AllowedResourcesConfig
{
    /**
     * Get allowed filters for collection entries query.
     *
     * @param  string  $configFile
     * @return array
     */
    public function allowedForCollectionEntries($configFile)
    {
        $config = config("statamic.{$configFile}.resources.collections", false);

        if (! $config) {
            return [];
        }

        if ($config === true) {
            return Collection::handles()->all();
        }

        // TODO: handle `'*' => ['enabled' => true]` config...

        return collect($config)
            ->mapWithKeys(fn ($value, $key) => is_int($key) ? [$value => true] : [$key => $value])
            ->reject(fn ($config, $resource) => $resource === '*')
            ->reject(fn ($config, $resource) => $config === false)
            ->keys()
            ->all();
    }

    /**
     * Get allowed filters for users query.
     *
     * @param  string  $configFile
     * @return array
     */
    public function allowedForUsers($configFile)
    {
        return config('statamic.graphql.resources.users', false);
    }
}
