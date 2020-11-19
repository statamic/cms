<?php

namespace Statamic\Assets;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\MountManager;
use Statamic\Imaging\ImageGenerator;

class Dimensions
{
    /**
     * @var Asset
     */
    private $asset;

    /**
     * @param $generator ImageGenerator
     */
    public function __construct(ImageGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function asset(Asset $asset)
    {
        $this->asset = $asset;

        return $this;
    }

    /**
     * Get the dimensions of an asset.
     *
     * @return array
     */
    public function get()
    {
        return $this->asset->isImage() ? $this->getImageDimensions() : [null, null];
    }

    /**
     * Get the width of the asset.
     *
     * @return int
     */
    public function width()
    {
        return array_get($this->get(), 0);
    }

    /**
     * Get the height of the asset.
     *
     * @return int
     */
    public function height()
    {
        return array_get($this->get(), 1);
    }

    /**
     * Get the dimensions.
     *
     * @return array
     */
    private function getImageDimensions()
    {
        // Since assets may be located on external platforms like Amazon S3, we can't simply
        // grab the dimensions. So we'll copy it locally and read the dimensions from there.
        $manager = new MountManager([
            'source' => $this->asset->disk()->filesystem()->getDriver(),
            'cache' => $cache = $this->getCacheFlysystem(),
        ]);

        $cachePath = "{$this->asset->containerId()}/{$this->asset->path()}";

        $source = "source://{$this->asset->path()}";
        $destination = "cache://{$cachePath}";
        if ($manager->has($destination)) {
            $manager->delete($destination);
        }
        $manager->copy($source, $destination);

        $size = getimagesize($cache->getAdapter()->getPathPrefix().$cachePath);

        $cache->delete($cachePath);

        return $size ? array_splice($size, 0, 2) : [null, null];
    }

    private function getCacheFlysystem()
    {
        $disk = 'dimensions-cache';

        config(["filesystems.disks.{$disk}" => [
            'driver' => 'local',
            'root' => storage_path('statamic/dimensions-cache'),
        ]]);

        return Storage::disk($disk)->getDriver();
    }
}
