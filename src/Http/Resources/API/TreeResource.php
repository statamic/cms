<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Site;
use Statamic\Structures\TreeBuilder;

class TreeResource extends JsonResource
{
    protected $fields;
    protected $depth;

    /**
     * Set selected fields.
     *
     * @param  array|null  $fields
     * @return $this
     */
    public function fields($fields = null)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Set max depth.
     *
     * @param  int|null  $depth
     * @return $this
     */
    public function maxDepth($depth = null)
    {
        $this->maxDepth = $depth;

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
            'structure' => $this->resource->structure(),
            'include_home' => true,
            'show_unpublished' => false,
            'site' => Site::default()->handle(),
            'fields' => $this->fields,
            'max_depth' => $this->maxDepth,
        ]);
    }
}
