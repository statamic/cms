<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\Resource;
use Statamic\Statamic;

class TermResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $apiUrl = Statamic::apiRoute('taxonomies.terms.show', [
            $this->resource->taxonomy()->handle(),
            $this->resource->slug(),
        ]);

        return array_merge($this->resource->toAugmentedArray(), [
            'api_url' => $apiUrl,
        ]);
    }
}
