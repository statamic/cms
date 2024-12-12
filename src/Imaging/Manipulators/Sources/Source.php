<?php

namespace Statamic\Imaging\Manipulators\Sources;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Imaging\Manipulator;
use Statamic\Facades\Image;
use Statamic\Support\Str;

abstract class Source
{
    public static function from(mixed $source): Source
    {
        if ($source instanceof Asset) {
            return new AssetSource($source);
        } elseif (Str::startsWith($source, ['http://', 'https://'])) {
            return new UrlSource($source);
        } elseif (Str::contains($source, '::')) {
            return new AssetIdSource($source);
        }

        return new PathSource($source);
    }

    abstract public function path(): string;

    public function asset(): ?Asset
    {
        return null;
    }

    public function manipulator(): Manipulator
    {
        return Image::driver();
    }
}
