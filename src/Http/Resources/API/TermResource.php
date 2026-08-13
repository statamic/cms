<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class TermResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $fields = collect($this->resource->selectedQueryColumns() ?? $this->resource->augmented()->keys());

        // Don't want these variables in API requests. The hierarchy term objects
        // are excluded since serializing them in full would recurse; a shallow
        // parent is appended below instead.
        $fields = $fields->reject(fn ($field) => in_array($field, ['entries', 'collection', 'parent', 'children', 'ancestors']));

        $with = $this->blueprint()
            ->fields()->all()
            ->filter->isRelationship()->keys()->all();

        $data = $this->resource
            ->toAugmentedCollection($fields->all())
            ->withRelations($with)
            ->withShallowNesting()
            ->toArray();

        if ($this->resource->taxonomy()->hierarchical()) {
            $data['parent'] = $this->resource->parent()?->toShallowAugmentedCollection()->toArray();
        }

        return $data;
    }
}
