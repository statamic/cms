<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $collection = $this->resource
            ->toAugmentedCollection()
            ->withShallowNesting();

        if (config('statamic.api.disable_urls', false)) {
            $collection->withoutUrls();
        }

        return $collection->toArray();
    }
}
