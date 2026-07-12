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

        // Don't want these variables in API requests.
        $fields = $fields->reject(fn ($field) => in_array($field, ['entries', 'collection']));

        $with = $this->blueprint()
            ->fields()->all()
            ->filter->isRelationship()->keys()->all();

        return $this->resource
            ->toAugmentedCollection($fields->all())
            ->withRelations($with)
            ->withShallowNesting()
            ->toArray();
    }
}
