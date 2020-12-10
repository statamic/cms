<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Site;
use Statamic\Structures\TreeBuilder;

class TreeResource extends JsonResource
{
    /**
     * Set selected fields.
     *
     * @param array|null $fields
     * @return $this
     */
    public function fields($fields = null)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return (new TreeBuilder)->build([
            'structure' => $this->resource->structure()->handle(),
            'include_home' => true,
            'site' => Site::default()->handle(),
            'fields' => $this->fields,
        ]);
    }
}
