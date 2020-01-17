<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\Resource;

class AssetResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $apiUrl = api_route('assets.show', [
            $this->resource->container()->handle(),
            $this->resource->path(),
        ]);

        return array_merge($this->resource->toAugmentedArray(), [
            'api_url' => $apiUrl,
        ]);
    }
}
