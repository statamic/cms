<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Statamic;

class TaxonomyResource extends JsonResource
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
            'api_url' => Statamic::apiRoute('taxonomies.show', [$this->resource->handle()]),
        ];
    }
}
