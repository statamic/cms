<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $fields = ['id', 'email', 'name', 'is_super', 'api_url'];

        // If fields have been selected, we want to only allow a subset of the allowed fields defined above.
        if ($selected = $this->resource->selectedQueryColumns()) {
            $diff = array_intersect($selected, $fields);
            $fields = empty($diff) ? $fields : $diff;
        }

        return $this->resource->toAugmentedCollection($fields);
    }
}
