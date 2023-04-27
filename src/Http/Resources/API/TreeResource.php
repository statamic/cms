<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Site;
use Statamic\Query\ItemQueryBuilder;
use Statamic\Structures\TreeBuilder;

class TreeResource extends JsonResource
{
    protected $fields;
    protected $depth;
    protected $site;
    protected $query;
    protected $maxDepth;

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
     * Set site.
     *
     * @param  string|null  $site
     * @return $this
     */
    public function site($site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Set query.
     *
     * @param  \Statamic\Structures\ItemQueryBuilder|null  $query
     * @return $this
     */
    public function query($query = null)
    {
        $this->query = $query;

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
            'query' => $this->query ?? (new ItemQueryBuilder)->whereIn('status', ['published', null]),
            'include_home' => true,
            'site' => $this->site ?? Site::default()->handle(),
            'fields' => $this->fields,
            'max_depth' => $this->maxDepth,
        ]);
    }
}
