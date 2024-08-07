<?php

namespace Statamic\Assets\Thumbnails;

use Statamic\Contracts\Assets\Asset;

class ImageThumbnailGenerator extends ThumbnailGenerator
{
    public function accepts(Asset $asset): bool
    {
        return $asset->isImage();
    }

    public function generate(Asset $asset, mixed $params = null): ?string
    {
        return cp_route('assets.thumbnails.show', [
            'encoded_asset' => base64_encode($asset->id()),
            'size' => $params['preset'] ?? null,
        ]);
    }
}
