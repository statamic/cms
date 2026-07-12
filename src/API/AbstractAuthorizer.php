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
        switch ($resource) {
            case 'collections':
                return Facades\Collection::handles()->all();
            case 'navs':
                return Facades\Nav::all()->map->handle()->all();
            case 'taxonomies':
                return Facades\Taxonomy::handles()->all();
            case 'assets':
                return Facades\AssetContainer::all()->map->handle()->all();
            case 'globals':
                return Facades\GlobalSet::all()->map->handle()->all();
            case 'forms':
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
