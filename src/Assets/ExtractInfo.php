<?php

namespace Statamic\Assets;

use Statamic\Contracts\Assets\Asset;

class ExtractInfo
{
    public function fromAsset(Asset $asset): array
    {
        $disk = $asset->disk()->filesystem();
        $path = $asset->path();

        return (new \getID3)->analyze($path, method_exists($disk, 'getSize') ? $disk->getSize($path) : $disk->fileSize($path), '', $disk->readStream($path));
    }
}
