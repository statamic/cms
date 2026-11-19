<?php

namespace Statamic\Imaging;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Imaging\UrlBuilder;
use Statamic\Facades\URL;
use Statamic\Support\Str;

abstract class ImageUrlBuilder implements UrlBuilder
{
    /**
     * @var Asset|string
     */
    protected $item;

    /**
     * Get the type of item.
     *
     * @return string
     */
    public function itemType()
    {
        if ($this->item instanceof Asset) {
            return 'asset';
        } elseif (URL::isAbsolute($this->item)) {
            return 'url';
        } elseif (Str::contains($this->item, '::')) {
            return 'id';
        }

        return 'path';
    }

    protected function withEncodedWatermark(array $params): array
    {
        if (isset($params['mark']) && $params['mark'] instanceof Asset) {
            $asset = $params['mark'];
            $params['mark'] = 'asset::'.Str::toBase64Url($asset->containerId().'/'.$asset->path());
        }

        return $params;
    }
}
