<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class TermResource extends JsonResource
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
            ->toAugmentedCollection($this->fields($request))
            ->withRelations($with)
            ->withShallowNesting()
            ->toArray();
    }

    private function fields($request)
    {
        // Don't want these variables in API requests.
        $excluded = ['entries', 'collection'];

        $requested = collect($this->requestedFields($request))
            ->reject(fn ($field) => in_array($field, $excluded));

        if ($requested->isNotEmpty()) {
            return $requested->all();
        }

        // Hierarchy fields are opt-in, the same way an entry's parent is. On a flat
        // taxonomy they may be user-defined blueprint fields, so leave them alone.
        if ($this->resource->taxonomy()->hasStructure()) {
            $excluded = [...$excluded, 'parent', 'children', 'ancestors', 'depth', 'is_root'];
        }

        return collect($this->resource->augmented()->keys())
            ->reject(fn ($field) => in_array($field, $excluded))
            ->all();
    }
}
