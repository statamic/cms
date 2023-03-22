<?php

namespace Statamic\API;

use Statamic\Facades;

abstract class AbstractAuthorizer
{
    /**
     * Get all possible handles for resource (for when evaluating `*` wildcards in config).
     *
     * @param  string  $resource
     * @return array|bool
     */
    protected function getAllHandlesForResource($resource)
    {
        if ($resource === 'collections') {
            return Facades\Collection::handles()->all();
        }

        if ($resource === 'navs') {
            return Facades\Nav::all()->map->handle()->all();
        }

        if ($resource === 'taxonomies') {
            return Facades\Taxonomy::handles()->all();
        }

        if ($resource === 'assets') {
            return Facades\AssetContainer::all()->map->handle()->all();
        }

        if ($resource === 'globals') {
            return Facades\GlobalSet::all()->map->handle()->all();
        }

        if ($resource === 'forms') {
            return Facades\Form::all()->map->handle()->all();
        }

        return false;
    }

    /**
     * Check if resource has sub-resources.
     *
     * @param  string  $resource
     * @return bool
     */
    protected function hasSubResources($resource)
    {
        return $this->getAllHandlesForResource($resource) !== false;
    }
}
