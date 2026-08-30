<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Statamic;

class CollectionResource extends JsonResource
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
            'structure' => $this->resource->structureHandle(),
            'mount' => $this->resource->mount()?->id(),
            'api_url' => Statamic::apiRoute('collections.show', [$this->resource->handle()]),
        ];
    }
}
