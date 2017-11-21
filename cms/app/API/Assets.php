<?php

namespace Statamic\API;

/**
 * @deprecated since 2.1
 */
class Assets
{
    /**
     * Get all assets
     *
     * @return \Statamic\Assets\AssetCollection
     * @deprecated since 2.1
     */
    public static function all()
    {
        \Log::notice('Assets::all() is deprecated. Use Asset::all()');

        return Asset::all();
    }

    /**
     * Get all the asset containers
     *
     * @return \Statamic\Contracts\Assets\AssetContainer[]
     * @deprecated since 2.1
     */
    public static function getContainers()
    {
        \Log::notice('Assets::getContainers() is deprecated. Use AssetContainer::all()');

        return AssetContainer::all()->all(); // double all to get an array
    }

    /**
     * Get an asset container by its ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Assets\AssetContainer
     * @deprecated since 2.1
     */
    public static function getContainer($id)
    {
        \Log::notice('Assets::getContainer() is deprecated. Use AssetContainer::find()');

        return AssetContainer::find($id);
    }

    /**
     * Get an asset container by its path
     *
     * @param string $path
     * @return \Statamic\Contracts\Assets\AssetContainer
     * @deprecated since 2.1
     */
    public static function getContainerByPath($path)
    {
        \Log::notice('Assets::getContainerByPath() is deprecated. Use AssetContainer::wherePath()');

        return AssetContainer::wherePath($path);
    }

    /**
     * Create an asset container
     *
     * @param string|null $driver
     * @return \Statamic\Contracts\Assets\AssetContainer
     * @deprecated since 2.1
     */
    public static function createContainer($driver = null)
    {
        \Log::notice('Assets::createContainer() is deprecated. Use AssetContainer::create()');

        return AssetContainer::create($driver);
    }
}
