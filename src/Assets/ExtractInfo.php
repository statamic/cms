<?php

namespace Statamic\Assets;

use Owenoj\LaravelGetId3\GetId3;
use Statamic\Contracts\Assets\Asset;

class ExtractInfo
{
    public function fromAsset(Asset $asset): array
    {
        return GetId3::fromDiskAndPath(
            $asset->container()->diskHandle(),
            $asset->path()
        )->extractInfo();
    }
}
