<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $asset = $this->resource;

        if ($site = $request->input('site')) {
            $asset = $asset->in($site);
        }

        $with = $asset->blueprint()
            ->fields()->all()
            ->filter->isRelationship()->keys()->all();

        return $asset
            ->toAugmentedCollection()
            ->withRelations($with)
            ->withShallowNesting()
            ->toArray();
    }
}
