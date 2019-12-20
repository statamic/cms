<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\Resource;
use Statamic\Facades\Action;
use Statamic\Support\Str;

class FolderAsset extends Resource
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

            $this->mergeWhen($this->isImage(), function () {
                return [
                    'is_image' => true,
                    'thumbnail' => $this->thumbnailUrl('small'),
                    'toenail' => $this->thumbnailUrl('large'),
                ];
            }),

            'actions' => Action::for($this->resource, [
                'container' => $this->container()->handle()
            ]),
        ];
    }
}
