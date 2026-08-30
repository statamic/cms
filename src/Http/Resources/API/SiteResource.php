<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class SiteResource extends JsonResource
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
            'name' => $this->resource->name(),
            'locale' => $this->resource->locale(),
            'short_locale' => $this->resource->shortLocale(),
            'url' => $this->resource->url(),
        ];
    }
}
