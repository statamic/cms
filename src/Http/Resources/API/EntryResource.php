<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class EntryResource extends JsonResource
{
    use ResolvesRequestedFields;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $with = $this->blueprint()
            ->fields()->all()
            ->filter->isRelationship()->keys()->all();

        return $this->resource
            ->toAugmentedCollection($this->requestedFields($request))
            ->withRelations($with)
            ->withShallowNesting()
            ->toArray();
    }
}
