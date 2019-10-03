<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\Resource;
use Statamic\Facades\Action;
use Statamic\Support\Str;

class Asset extends Resource
{
    public function toArray($request)
    {
        return [
            $this->merge($this->resource->toArray()),

            'size_formatted' => Str::fileSizeForHumans($this->size(), 0),
            'last_modified_relative' => $this->lastModified()->diffForHumans(),

            $this->mergeWhen($this->isImage(), function () {
                return [
                    'thumbnail' => $this->thumbnailUrl('small'),
                    'toenail' => $this->thumbnailUrl('large'),
                ];
            }),

            'actions' => Action::for('asset-browser', [
                'container' => $this->container()->handle()
            ], $this->resource),
        ];
    }
}
