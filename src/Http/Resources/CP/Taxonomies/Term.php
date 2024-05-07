<?php

namespace Statamic\Http\Resources\CP\Taxonomies;

use Illuminate\Http\Resources\Json\JsonResource;

class Term extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->resource->id(),
            'reference' => $this->resource->reference(),
            'title' => $this->resource->value('title'),
            'permalink' => $this->resource->absoluteUrl(),
            'edit_url' => $this->resource->editUrl(),
        ];

        return ['data' => $data];
    }
}
