<?php

namespace Statamic\Contracts\Assets;

interface ThumbnailGenerator
{
    public function accepts(Asset $asset): bool;

    public function generate(Asset $asset, mixed $params): ?string;
}
