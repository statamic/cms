<?php

namespace Statamic\Assets\Thumbnails;

use Statamic\Contracts\Assets\Asset;

class SvgThumbnailGenerator extends ThumbnailGenerator
{
    public function accepts(Asset $asset): bool {
        return $asset->isSvg();
    }

    public function generate(Asset $asset, mixed $params): ?string {
        if ($url = $asset->url()) {
            return $url;
        }

        return cp_route('assets.svgs.show', ['encoded_asset' => base64_encode($asset->id())]);
    }
}
