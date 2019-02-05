<?php

namespace Statamic\API\Endpoint;

use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\Site;
use Statamic\API\AssetContainer;
use Statamic\Assets\AssetCollection;
use Statamic\Contracts\Assets\AssetFactory;
use Statamic\Contracts\Assets\Asset as AssetContract;

class Asset
{
    /**
     * Get an asset by a URL or ID
     *
     * @param string $asset
     * @return \Statamic\Contracts\Assets\Asset
     */
    public function find($asset)
    {
        if (Str::contains($asset, '::')) {
            return self::whereId($asset);
        }

        return self::whereUrl($asset);
    }

    /**
     * Get an asset by ID
     *
     * @param string $id  An asset ID in the form of "container_id::asset_path""
     * @return \Statamic\Contracts\Assets\Asset
     */
    public function whereId($id)
    {
        list($container_id, $path) = explode('::', $id);

        // If a container can't be found, we'll assume there's no asset.
        if (! $container = AssetContainer::find($container_id)) {
            return null;
        }

        return $container->asset($path);
    }

    /**
     * Get an asset by url
     *
     * @param string $url
     * @return \Statamic\Contracts\Assets\Asset
     */
    private static function whereUrl($url)
    {
        // If a container can't be resolved, we'll assume there's no asset.
        if (! $container = self::resolveContainerFromUrl($url)) {
            return null;
        }

        $siteUrl = rtrim(Site::current()->absoluteUrl(), '/');
        $containerUrl = $container->url();

        if (starts_with($containerUrl, '/')) {
            $containerUrl = $siteUrl . $containerUrl;
        }

        if (starts_with($containerUrl, $siteUrl)) {
            $url = $siteUrl . $url;
        }

        $path = str_after($url, $containerUrl);

        return $container->asset($path);
    }

    /**
     * Find an asset container given an asset URL
     *
     * @param string $url
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    private static function resolveContainerFromUrl($url)
    {
        return AssetContainer::all()->sortBy(function ($container) {
            return strlen($container->url());
        })->first(function ($container, $id) use ($url) {
            return starts_with($url, $container->url())
                || starts_with(URL::makeAbsolute($url), $container->url());
        });
    }

    /**
     * Get all assets
     *
     * @return AssetCollection
     */
    public function all()
    {
        return collect_assets(AssetContainer::all()->flatMap(function ($container) {
            return $container->assets();
        }));
    }

    /**
     * Get all assets in a folder
     *
     * @param string $folder
     * @param string $container
     * @return AssetCollection
     */
    public function whereFolder($folder, $container)
    {
        return AssetContainer::find($container)->assets($folder);
    }

    /**
     * Get all assets in a container
     *
     * @param string $container
     * @return AssetCollection
     */
    public function whereContainer($container)
    {
        return AssetContainer::find($container)->assets();
    }

    /**
     * Get an asset by its path
     *
     * @param string      $path
     * @return Asset
     */
    public function wherePath($path)
    {
        return self::all()->filter(function ($asset) use ($path) {
            return $asset->resolvedPath() === $path;
        })->first();
    }

    /**
     * @param string|null $path
     * @return \Statamic\Contracts\Assets\AssetFactory
     */
    public function create($path = null)
    {
        return app(AssetFactory::class)->create($path);
    }

    public function make()
    {
        return new \Statamic\Assets\Asset;
    }

    /**
     * Get a raw asset by its UUID
     *
     * @param string      $uuid
     * @return \Statamic\Contracts\Assets\Asset
     * @deprecated since 2.1
     */
    public function uuidRaw($uuid)
    {
        \Log::notice('Asset::uuidRaw() is deprecated. Use Asset::find()');

        return self::find($uuid);
    }

    /**
     * Get an asset by its UUID
     *
     * @param string      $uuid
     * @return array
     * @deprecated since 2.1
     */
    public function uuid($uuid)
    {
        \Log::notice('Asset::uuid() is deprecated. Use Asset::find()->toArray()');

        return self::find($uuid)->toArray();
    }

    /**
     * Get an asset by its path
     *
     * @param string      $path
     * @return Asset
     * @deprecated since 2.1
     */
    public function path($path)
    {
        \Log::notice('Asset::path() is deprecated. Use Asset::wherePath()');

        return self::wherePath($path);
    }
}
