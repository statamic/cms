<?php

namespace Statamic\Http\Resources\CP\Taxonomies;

use Illuminate\Http\Resources\Json\Resource;

class Term extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id(),
            'title' => $this->resource->value('title'),
            'permalink' => $this->resource->absoluteUrl(),
        ];
    }
}
