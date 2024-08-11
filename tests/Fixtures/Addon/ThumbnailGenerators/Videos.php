<?php

namespace Tests\Fixtures\Addon\ThumbnailGenerators;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\ThumbnailGenerator;

class Videos implements ThumbnailGenerator
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
