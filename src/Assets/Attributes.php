<?php

namespace Statamic\Assets;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\MountManager;
use Owenoj\LaravelGetId3\GetId3;
use Statamic\Imaging\ImageGenerator;
use Statamic\Support\Arr;

class Attributes
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
     * Get the attributes of an asset.
     *
     * @return array
     */
    public function get()
    {
        if ($this->asset->isAudio()) {
            return $this->getAudioAttributes();
        }

        if ($this->asset->isImage()) {
            return $this->getImageAttributes();
        }

        if ($this->asset->isSvg()) {
            return $this->getSvgAttributes();
        }

        if ($this->asset->isVideo()) {
            return $this->getVideoAttributes();
        }

        return [null, null, null];
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
     * Get the attributes of a sound.
     *
     * @return array
     */
    private function getAudioAttributes()
    {
        $id3 = GetId3::fromDiskAndPath(
            $this->asset->container()->diskHandle(),
            $this->asset->basename()
        )->extractInfo();

        $length = Arr::get($id3, 'playtime_seconds', 0);

        return [null, null, $length];
    }

    /**
     * Get the attributes of an image.
     *
     * @return array
     */
    private function getImageAttributes()
    {
        // Since assets may be located on external platforms like Amazon S3, we can't simply
        // grab the attributes. So we'll copy it locally and read the attributes from there.
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
            $size[2] = 0;
        } catch (\Exception $e) {
            $size = [0, 0, 0];
        } finally {
            $cache->delete($cachePath);
        }

        return $size ? array_splice($size, 0, 3) : [0, 0, 0];
    }

    /**
     * Get the attributes of an SVG.
     *
     * @return array
     */
    private function getSvgAttributes()
    {
        // Since assets may be located on external platforms like Amazon S3, we can't simply
        // grab the attributes. So we'll copy it locally and read the attributes from there.
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
            return [(float) $svg['width'], (float) $svg['height'], 0];
        } elseif ($svg['viewBox']) {
            $viewBox = preg_split('/[\s,]+/', $svg['viewBox'] ?: '');

            return [$viewBox[2], $viewBox[3], 0];
        }

        return [300, 150, 0];
    }

    /**
     * Get the attributes of a sound.
     *
     * @return array
     */
    private function getVideoDimensions()
    {
        $id3 = GetId3::fromDiskAndPath(
            $this->asset->container()->diskHandle(),
            $this->asset->basename()
        )->extractInfo();

        $width = Arr::get($id3, 'video.resolution_x');
        $height = Arr::get($id3, 'video.resolution_y');
        $length = Arr::get($id3, 'playtime_seconds');

        return [$width, $height, $length];
    }

    private function getCacheFlysystem()
    {
        $disk = 'attributes-cache';

        config(["filesystems.disks.{$disk}" => [
            'driver' => 'local',
            'root' => storage_path('statamic/attributes-cache'),
        ]]);

        return Storage::disk($disk)->getDriver();
    }
}
