<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Statamic;

class NavResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'handle' => $this->resource->handle(),
            'title' => $this->resource->title(),
            'max_depth' => $this->resource->maxDepth(),
            'expects_root' => (bool) $this->resource->expectsRoot(),
            'api_url' => Statamic::apiRoute('navs.show', [$this->resource->handle()]),
        ];
    }
}
