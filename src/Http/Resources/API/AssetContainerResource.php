<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Statamic;

class AssetContainerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'handle' => $this->resource->handle(),
            'title' => $this->resource->title(),
            'api_url' => Statamic::apiRoute('asset-containers.show', [$this->resource->handle()]),
        ];
    }
}
