<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Support\Str;

class FolderAsset extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id(),
            'basename' => $this->basename(),
            'extension' => $this->extension(),
            'url' => $this->absoluteUrl(),

            'size_formatted' => Str::fileSizeForHumans($this->size(), 0),
            'last_modified_relative' => $this->lastModified()->diffForHumans(),

            $this->mergeWhen($this->isImage() || $this->isSvg(), function () {
                return [
                    'is_image' => true,
                    'thumbnail' => $this->thumbnailUrl('small'),
                    'alt' => $this->alt,
                ];
            }),

            'actions' => Action::for($this->resource, [
                'container' => $this->container()->handle(),
                'folder' => $this->folder(),
            ]),
        ];
    }
}
