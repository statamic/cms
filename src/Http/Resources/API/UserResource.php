<?php

namespace Statamic\Http\Resources\API;

use Illuminate\Http\Resources\Json\Resource;
use Statamic\Statamic;

class UserResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id(),
            'email' => $this->resource->email(),
            'name' => $this->resource->get('name'),
            'is_super' => $this->resource->isSuper(),
            'api_url' => Statamic::apiRoute('users.show', $this->resource->id()),
        ];
    }
}
