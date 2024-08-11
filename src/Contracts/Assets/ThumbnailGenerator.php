<?php

namespace Statamic\Contracts\Assets;

use Statamic\Contracts\Assets\Asset;

interface ThumbnailGenerator
{
    public function accepts(Asset $asset): bool;

    public function generate(Asset $asset, mixed $params): ?string;
}
