<?php

namespace Statamic\Assets\Thumbnails;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\ThumbnailGenerator;

class SvgThumbnailGenerator implements ThumbnailGenerator
{
    public function accepts(Asset $asset): bool
    {
        return $asset->isSvg();
    }

    public function generate(Asset $asset, mixed $params = null): ?string
    {
        if ($url = $asset->url()) {
            return $url;
        }

        return cp_route('assets.svgs.show', ['encoded_asset' => base64_encode($asset->id())]);
    }
}
