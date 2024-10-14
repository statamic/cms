<?php

namespace Statamic\Imaging\Manipulators\Sources;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Imaging\Manipulator;

class AssetSource extends Source
{
    public function __construct(private readonly Asset $asset)
    {
        //
    }

    public function asset(): Asset
    {
        return $this->asset;
    }

    public function path(): string
    {
        return $this->asset->path();
    }

    public function manipulator(): Manipulator
    {
        return $this->asset->container()->imageManipulator();
    }
}
