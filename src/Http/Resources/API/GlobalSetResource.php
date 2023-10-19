<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Statamic;

class GlobalSetResource extends JsonResource
{
    /** @deprecated */
    private static $relations = false;

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $collection = $this->resource
            ->toAugmentedCollection()
            ->merge([
                'handle' => $this->resource->handle(),
                'api_url' => Statamic::apiRoute('globals.show', [$this->resource->handle()]),
            ]);

        if (static::$relations) {
            $with = $this->resource->blueprint()
                ->fields()->all()
                ->filter->isRelationship()->keys()->all();

            $collection->withRelations($with);
        }

        return $collection
            ->withShallowNesting()
            ->toArray();
    }

    /** @deprecated */
    public static function withRelations()
    {
        static::$relations = true;
    }
}
