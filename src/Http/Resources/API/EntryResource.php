<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\Resource;

class EntryResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $apiUrl = api_route('collections.entries.show', [
            $this->resource->collection()->handle(),
            $this->resource->id(),
        ]);

        return array_merge($this->resource->toAugmentedArray(), [
            'api_url' => $apiUrl,
        ]);
    }
}
