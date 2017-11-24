<?php

namespace Statamic\Imaging;

use Statamic\API\Str;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Imaging\UrlBuilder;

abstract class ImageUrlBuilder implements UrlBuilder
{
    /**
     * @var Asset|string
     */
    protected $item;

    /**
     * Get the type of item
     *
     * @return string
     */
    public function itemType()
    {
        if ($this->item instanceof Asset) {
            return 'asset';
        } elseif (Str::startsWith($this->item, ['http://', 'https://'])) {
            return 'url';
        } elseif (Str::contains($this->item, '::')) {
            return 'id';
        }

        return 'path';
    }
}
