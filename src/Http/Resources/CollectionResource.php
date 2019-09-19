<?php

namespace Statamic\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CollectionResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'path' => $this->resource->path(),
            'route' => $this->resource->get('route'),
        ];
    }
}
