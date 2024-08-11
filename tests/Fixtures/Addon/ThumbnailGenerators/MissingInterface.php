<?php

namespace Tests\Fixtures\Addon\ThumbnailGenerators;

use Statamic\Contracts\Assets\Asset;

class MissingInterface
{
    public function accepts(Asset $asset): bool
    {
        return true;
    }

    public function generate(Asset $asset, mixed $params): ?string
    {
        return '';
    }
}
