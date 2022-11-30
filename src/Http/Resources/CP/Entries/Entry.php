<?php

namespace Statamic\Http\Resources\CP\Entries;

use Illuminate\Http\Resources\Json\JsonResource;

class Entry extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->resource->id(),
            'title' => $this->resource->value('title'),
            'permalink' => $this->resource->absoluteUrl(),
            'published' => $this->resource->published(),
            'status' => $this->resource->status(),
            'private' => $this->resource->private(),
            'edit_url' => $this->resource->editUrl(),
            'collection' => [
                'title' => $this->resource->collection()->title(),
                'handle' => $this->resource->collection()->handle(),
            ],
        ];

        return ['data' => $data];
    }
}
