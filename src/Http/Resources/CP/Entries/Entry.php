<?php

namespace Statamic\Http\Resources\CP\Entries;

use Illuminate\Http\Resources\Json\JsonResource;

class Entry extends JsonResource
{
    private $successMessage;

    public function __construct($resource, string $successMessage = null)
    {
        parent::__construct($resource);
        $this->successMessage = $successMessage;
    }

    public function toArray($request)
    {
        return [
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
            'cp_message_success' => $this->successMessage,
        ];
    }
}
