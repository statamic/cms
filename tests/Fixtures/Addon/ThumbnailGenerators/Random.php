<?php

namespace Tests\Fixtures\Addon\ThumbnailGenerators;

use Statamic\Assets\Thumbnails\ThumbnailGenerator;
use Statamic\Contracts\Assets\Asset;

class Random extends ThumbnailGenerator
{
    public function accepts(Asset $asset): bool {
        return true;
    }

    public function generate(Asset $asset, mixed $params): ?string {
        return 'https://picsum.photos/200/300';
    }
}
