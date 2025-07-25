<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Support\Fluent;
use Statamic\Support\Traits\Hookable;

trait HasThumbnails
{
    use Hookable;

    private function thumbnails(): array
    {
        $data = match (true) {
            $this->isImage() || $this->isSvg() => $this->getImageThumbnail(),
            $this->isVideo() && config('statamic.assets.video_thumbnails', true) => $this->getVideoThumbnail(),
            default => ['thumbnail' => null],
        };

        return array_merge($data, $this->runAssetHook() ?? []);
    }

    private function getImageThumbnail(): array
    {
        return [
            'is_image' => true,
            'preview' => $this->previewUrl(),
            'thumbnail' => $this->thumbnailUrl('small'),
            'can_be_transparent' => $this->isSvg() || $this->extensionIsOneOf(['svg', 'png', 'webp', 'avif']),
            'alt' => $this->alt,
            'orientation' => $this->orientation(),
        ];
    }

    protected function previewUrl()
    {
        // Public asset containers can use their regular URLs.
        // Private ones don't have URLs so we'll generate an actual-size "thumbnail".
        return $this->container()->accessible() ? $this->url() : $this->thumbnailUrl();
    }

    private function getVideoThumbnail(): array
    {
        return [
            'thumbnail' => $this->thumbnailUrl('small'),
        ];
    }

    private function runAssetHook(): array
    {
        $payload = $this->runHooksWith('asset', [
            'data' => new Fluent,
        ]);

        return $payload->data->toArray();
    }
}
