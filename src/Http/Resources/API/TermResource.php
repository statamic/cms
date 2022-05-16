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
        $fields = $this->resource->selectedQueryColumns() ?? $this->resource->augmented()->keys();

        // Don't want the 'entries' variable in API requests.
        $fields = array_diff($fields, ['entries']);

        $with = $this->blueprint()
            ->fields()->all()
            ->filter->isRelationship()->keys()->all();

        return $this->resource
            ->toAugmentedCollection($fields)
            ->withRelations($with)
            ->withShallowNesting()
            ->toArray();
    }
}
