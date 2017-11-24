<?php

namespace Statamic\Data\Services;

use Statamic\API\Str;
use Statamic\API\Helper;

abstract class BaseService extends AbstractService
{
    /**
     * Get all items
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return $this->repo()->getItems();
    }

    /**
     * Get an item by ID
     *
     * @param string $id
     * @return mixed
     */
    public function id($id)
    {
        return $this->repo()->getItem($id);
    }

    /**
     * Check if an item exists by ID
     *
     * @param string $id
     */
    public function exists($id)
    {
        return $this->repo()->getIds()->has($id);
    }

    /**
     * Get an item by URI
     *
     * @param string $uri
     * @return mixed
     */
    public function uri($uri)
    {
        $uri = Str::ensureLeft($uri, '/');

        $id = $this->repo()->getIdByUri($uri);

        return $this->repo()->getItem($id);
    }

    /**
     * Check if an item exists by URI
     *
     * @param string $uri
     */
    public function uriExists($uri)
    {
        return $this->repo()->getUris()->flip()->has($uri);
    }

    /**
     * Get multiple items by their URLs
     *
     * @param array $urls
     * @return \Illuminate\Support\Collection
     */
    public function urls($urls)
    {
        return collect(Helper::ensureArray($urls))->map(function ($url) {
            return $this->url($url);
        });
    }
}
