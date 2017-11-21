<?php

namespace Statamic\Assets;

use Statamic\API\URL;
use Statamic\API\Path;
use Statamic\API\Cache;

class DimensionBuilder
{
    /**
     * @var Asset
     */
    private $asset;

    /**
     * @var string
     */
    private $cacheKey;

    /**
     * @var int
     */
    private $cacheLength = 60;

    /**
     * @param Asset $asset
     */
    public function __construct(Asset $asset)
    {
        $this->asset = $asset;

        $this->cacheKey = 'assets.dimensions.' . $this->asset->containerId() . '.' . $this->asset->path();
    }

    /**
     * Get the dimensions of an asset, and cache them.
     *
     * @return array
     */
    public function dimensions()
    {
        if (! $this->asset->isImage()) {
            return [null, null];
        }

        if ($cached = $this->cached()) {
            return $cached;
        }

        $pathMethod = 'get' . ucfirst($this->asset->driver()) . 'Path';

        $dimensions = $this->getImageDimensions($this->$pathMethod());

        $this->cache($dimensions);

        return $dimensions;
    }

    /**
     * Get the width of the asset
     *
     * @return int
     */
    public function width()
    {
        return array_get($this->dimensions(), 0);
    }

    /**
     * Get the height of the asset
     *
     * @return int
     */
    public function height()
    {
        return array_get($this->dimensions(), 1);
    }

    /**
     * Get the path of an asset within a local driven container
     *
     * @return string
     */
    private function getLocalPath()
    {
        return Path::assemble(
            $this->asset->disk()->filesystem()->getAdapter()->getPathPrefix(),
            $this->asset->path()
        );
    }

    /**
     * Get the path of an asset within an Amazon S3 driven container
     *
     * @return string
     */
    private function getS3Path()
    {
        return URL::encode($this->asset->url());
    }

    /**
     * Get the dimensions
     *
     * @param string $path
     * @return array
     */
    private function getImageDimensions($path)
    {
        $size = getimagesize($path);

        // getimagesize may return more than two keys. We only want the first two.
        return array_splice($size, 0, 2);
    }

    /**
     * Get the cached dimension value
     *
     * @return array|null
     */
    private function cached()
    {
        return Cache::get($this->cacheKey);
    }

    /**
     * Cache the dimensions
     *
     * @param array $dimensions
     * @return void
     */
    private function cache($dimensions)
    {
        Cache::put($this->cacheKey, $dimensions, $this->cacheLength);
    }
}
