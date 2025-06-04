<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Fluent;
use Statamic\Facades\Action;
use Statamic\Support\Str;
use Statamic\Support\Traits\Hookable;

class FolderAsset extends JsonResource
{
    use Hookable;

    private function getImageThumbnail()
    {
        return [
            'is_image' => true,
            'thumbnail' => $this->thumbnailUrl('small'),
            'can_be_transparent' => $this->isSvg() || $this->extensionIsOneOf(['svg', 'png', 'webp', 'avif']),
            'alt' => $this->alt,
            'orientation' => $this->orientation(),
        ];
    }

    private function getVideoThumbnail()
    {
        return [
            'thumbnail' => $this->thumbnailUrl('small'),
        ];
    }

    private function thumbnails()
    {
        if ($this->isImage() || $this->isSvg()) {
            return $this->getImageThumbnail();
        } elseif (config('statamic.assets.video_thumbnails', true) && $this->isVideo()) {
            return $this->getVideoThumbnail();
        }

        return ['thumbnail' => null];
    }

    private function runAssetHook()
    {
        $payload = $this->runHooksWith('asset', [
            'data' => new Fluent,
        ]);

        return $payload->data->toArray();
    }

    public function toArray($request)
    {
        $hookData = $this->runAssetHook();

        return [
            'id' => $this->id(),
            'basename' => $this->basename(),
            'extension' => $this->extension(),
            'url' => $this->absoluteUrl(),
            'size_formatted' => Str::fileSizeForHumans($this->size(), 0),
            'last_modified_relative' => $this->lastModified()->diffForHumans(),

            'actions' => Action::for($this->resource, [
                'container' => $this->container()->handle(),
                'folder' => $this->folder(),
            ]),

            $this->mergeWhen(! empty($hookData), $hookData),
            $this->merge($this->thumbnails()),
        ];
    }
}
