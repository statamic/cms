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
        if ($this->asset->isImage()) {
            return $this->getImageDimensions();
        } elseif ($this->asset->isSvg()) {
            return $this->getSvgDimensions();
        }

        return [null, null];
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
     * Get the dimensions of an image.
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

        if ($manager->has($destination = "cache://{$cachePath}")) {
            $manager->delete($destination);
        }

        $manager->copy("source://{$this->asset->path()}", $destination);

        try {
            $size = getimagesize($cache->getAdapter()->getPathPrefix().$cachePath);
        } catch (\Exception $e) {
            $size = [0, 0];
        } finally {
            $cache->delete($cachePath);
        }

        return $size ? array_splice($size, 0, 2) : [0, 0];
    }

    /**
     * Get the dimensions of an SVG.
     *
     * @return array
     */
    private function getSvgDimensions()
    {
        // Since assets may be located on external platforms like Amazon S3, we can't simply
        // grab the dimensions. So we'll copy it locally and read the dimensions from there.
        $manager = new MountManager([
            'source' => $this->asset->disk()->filesystem()->getDriver(),
            'cache' => $cache = $this->getCacheFlysystem(),
        ]);

        $cachePath = "{$this->asset->containerId()}/{$this->asset->path()}";

        if ($manager->has($destination = "cache://{$cachePath}")) {
            $manager->delete($destination);
        }

        $manager->copy("source://{$this->asset->path()}", $destination);

        $svg = simplexml_load_file($cache->getAdapter()->getPathPrefix().$cachePath);

        $cache->delete($cachePath);

        if ($svg['width'] && $svg['height']
            && is_numeric((string) $svg['width'])
            && is_numeric((string) $svg['height'])) {
            return [(float) $svg['width'], (float) $svg['height']];
        } elseif ($svg['viewBox']) {
            $viewBox = preg_split('/[\s,]+/', $svg['viewBox'] ?: '');

            return [$viewBox[2], $viewBox[3]];
        }

        return [300, 150];
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
