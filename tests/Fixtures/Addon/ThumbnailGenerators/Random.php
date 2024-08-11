<?php

namespace Tests\Fixtures\Addon\ThumbnailGenerators;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\ThumbnailGenerator;

class Random implements ThumbnailGenerator
{
    public function accepts(Asset $asset): bool
    {
        return true;
    }

    public function generate(Asset $asset, mixed $params): ?string
    {
        return 'https://picsum.photos/200/300';
    }
}
