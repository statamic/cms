<?php

namespace Statamic\API\Endpoint;

use Statamic\Data\Services\AssetContainersService;
use Statamic\Contracts\Assets\AssetContainerFactory;

class AssetContainer
{
    /**
     * Get all asset containers
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return app(AssetContainersService::class)->all();
    }

    /**
     * Get an asset container by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function find($id)
    {
        return app(AssetContainersService::class)->id($id);
    }

    /**
     * Get an asset container by path
     *
     * @param string $path
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function wherePath($path)
    {
        return self::all()->filter(function ($container) use ($path) {
            return $container->path() == $path;
        })->first();
    }

    /**
     * Create an asset container
     *
     * @param string|null $driver
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function create($driver = null)
    {
        return app(AssetContainerFactory::class)->create($driver);
    }
}