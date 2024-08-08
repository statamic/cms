<?php

namespace Statamic\Assets\Thumbnails;

use Statamic\Contracts\Assets\Asset;

abstract class ThumbnailGenerator
{
    abstract public function accepts(Asset $asset): bool;

    abstract public function generate(Asset $asset, mixed $params): ?string;

    /**
     * Register generator with Statamic.
     */
    public static function register()
    {
        if (! app()->has('statamic.thumbnail-generators')) {
            return;
        }

        app('statamic.thumbnail-generators')[] = static::class;
    }
}
