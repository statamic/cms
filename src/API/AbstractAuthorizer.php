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
