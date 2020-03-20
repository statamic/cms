<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JSONResource;

class TermResource extends JSONResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return array_merge($this->resource->toAugmentedArray(), [
            'api_url' => $this->resource->apiUrl(),
        ]);
    }
}
