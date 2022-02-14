<?php

namespace Statamic\Assets;

use Facades\Statamic\Assets\ExtractInfo;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\MountManager;
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

        return [];
    }

    /**
     * Get the attributes of a sound.
     *
     * @return array
     */
    private function getAudioAttributes()
    {
        $id3 = ExtractInfo::fromAsset($this->asset);

        $length = Arr::get($id3, 'playtime_seconds', 0);

        return ['duration' => $length];
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
            [$width, $height] = getimagesize($cache->getAdapter()->getPathPrefix().$cachePath);
            $size = compact('width', 'height');
        } catch (\Exception $e) {
            $size = [];
        } finally {
            $cache->delete($cachePath);
        }

        return $size;
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
            return ['width' => (float) $svg['width'], 'height' => (float) $svg['height']];
        } elseif ($svg['viewBox']) {
            [,,$width, $height] = preg_split('/[\s,]+/', $svg['viewBox'] ?: '');

            return compact('width', 'height');
        }

        return ['width' => 300, 'height' => 150];
    }

    /**
     * Get the attributes of a video.
     *
     * @return array
     */
    private function getVideoAttributes()
    {
        $id3 = ExtractInfo::fromAsset($this->asset);

        return [
            'width' => Arr::get($id3, 'video.resolution_x'),
            'height' => Arr::get($id3, 'video.resolution_y'),
            'duration' => Arr::get($id3, 'playtime_seconds'),
        ];
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
