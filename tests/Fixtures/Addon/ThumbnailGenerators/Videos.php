<?php

namespace Tests\Fixtures\Addon\ThumbnailGenerators;

use Statamic\Assets\Thumbnails\ThumbnailGenerator;
use Statamic\Contracts\Assets\Asset;

class Videos extends ThumbnailGenerator
{
    public function accepts(Asset $asset): bool
    {
        return $asset->isVideo();
    }

    public function generate(Asset $asset, mixed $params): ?string
    {
        return '/custom/video/thumb/'.base64_encode($asset->id());
    }
}
