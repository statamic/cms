<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\Resource;
use Statamic\Statamic;

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
        $apiUrl = Statamic::apiRoute('assets.show', [
            $this->resource->container()->handle(),
            $this->resource->path(),
        ]);

        return array_merge($this->resource->toAugmentedArray(), [
            'api_url' => $apiUrl,
        ]);
    }
}
