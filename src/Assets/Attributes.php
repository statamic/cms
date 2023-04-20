<?php

namespace Statamic\Assets;

use Facades\Statamic\Assets\ExtractInfo;
use Statamic\Support\Arr;

class Attributes
{
    /**
     * @var Asset
     */
    private $asset;

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
        return \Facades\Statamic\Imaging\Attributes::from($this->asset->disk()->filesystem()->getDriver(), $this->asset->path());
    }

    /**
     * Get the attributes of an SVG.
     *
     * @return array
     */
    private function getSvgAttributes()
    {
        return \Facades\Statamic\Imaging\Attributes::from($this->asset->disk()->filesystem()->getDriver(), $this->asset->path());
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
}
