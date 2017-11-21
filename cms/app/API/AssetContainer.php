<?php

namespace Statamic\API;

use Statamic\Data\Services\AssetContainersService;
use Statamic\Contracts\Assets\AssetContainerFactory;

class AssetContainer
{
    /**
     * Get all asset containers
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all()
    {
        return app(AssetContainersService::class)->all();
    }

    /**
     * Get an asset container by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public static function find($id)
    {
        return app(AssetContainersService::class)->id($id);
    }

    /**
     * Get an asset container by path
     *
     * @param string $path
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public static function wherePath($path)
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
    public static function create($driver = null)
    {
        return app(AssetContainerFactory::class)->create($driver);
    }
}