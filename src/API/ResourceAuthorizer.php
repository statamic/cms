<?php

namespace Statamic\API;

use Statamic\Support\Arr;

class ResourceAuthorizer extends AbstractAuthorizer
{
    /**
     * Check if resource is allowed to be queried.
     *
     * For example, is the `users` resource allowed to be queried?
     *
     * @param  string  $configFile
     * @param  string  $queriedResource
     * @return bool
     */
    public function isAllowed($configFile, $queriedResource)
    {
        if (config("statamic.{$configFile}.resources.{$queriedResource}", false) === 'false') {
            return false;
        }

        if ($this->hasSubResources($queriedResource)) {
            return (bool) $this->allowedSubResources($configFile, $queriedResource);
        }

        return true;
    }

    /**
     * Get allowed sub-resource(s) for the resource being queried.
     *
     * For example, which specific collections are allowed to be queried within the `collections` resource?
     *
     * @param  string  $configFile
     * @param  string  $queriedResource
     * @return array
     */
    public function allowedSubResources($configFile, $queriedResource)
    {
        $config = config("statamic.{$configFile}.resources.{$queriedResource}", false);

        if (! $config) {
            return [];
        }

        if ($config === true || Arr::get($config, '*.enabled') === true) {
            return $this->getAllHandlesForResource($queriedResource);
        }

        return collect($config)
            ->mapWithKeys(fn ($value, $key) => is_int($key) ? [$value => true] : [$key => $value])
            ->reject(fn ($config, $resource) => $resource === '*')
            ->reject(fn ($config, $resource) => $config === false)
            ->keys()
            ->all();
    }
}
