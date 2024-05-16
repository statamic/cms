<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;

class Folder extends JsonResource
{
    protected $withChildFolders = false;

    public function withChildFolders()
    {
        $this->withChildFolders = true;

        return $this;
    }

    public function toArray($request)
    {
        return [
            $this->merge($this->resource->toArray()),

            'actions' => Action::for($this->resource, [
                'container' => $this->container()->handle(),
                'folder' => $this->path(),
            ]),

            $this->mergeWhen($this->withChildFolders, function () {
                return ['folders' => Folder::collection($this->assetFolders()->values())];
            }),
        ];
    }
}
