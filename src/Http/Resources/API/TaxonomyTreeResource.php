<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Site;
use Statamic\Facades\Term;

class TaxonomyTreeResource extends JsonResource
{
    protected $fields;
    protected $site;
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
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $pages = $this->resource->structure()->tree()->pages()->all();

        return $this->buildTree($pages, $request);
    }

    private function buildTree($pages, $request, $depth = 1)
    {
        if ($this->maxDepth && $depth > $this->maxDepth) {
            return [];
        }

        return collect($pages)->map(function ($page) use ($request, $depth) {
            if (! $term = Term::find($this->resource->handle().'::'.$page->id())) {
                return null;
            }

            $term = $term->in($this->site ?? Site::default()->handle());

            if ($this->fields) {
                $term = $term->selectedQueryColumns($this->fields);
            }

            return [
                'term' => (new TermResource($term))->resolve($request),
                'depth' => $depth,
                'children' => $this->buildTree($page->pages()->all(), $request, $depth + 1),
            ];
        })->filter()->values()->all();
    }
}
