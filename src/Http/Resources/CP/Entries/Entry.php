<?php

namespace Statamic\Http\Resources\CP\Entries;

use Illuminate\Http\Resources\Json\Resource;

class Entry extends Resource
{
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id(),
            'title' => $this->resource->value('title'),
            'permalink' => $this->resource->absoluteUrl(),
            'published' => $this->resource->published(),
            'private' => $this->resource->private(),
            'edit_url' => $this->resource->editUrl(),
        ];
    }
}
