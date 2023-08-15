<?php

namespace Statamic\Http\Resources\CP\Nav;

use Illuminate\Http\Resources\Json\JsonResource;

class NavItem extends JsonResource
{
    public function toArray($request)
    {
        if ($children = $this->resource->resolveChildren()->children()) {
            $children = self::collection($children);
        }

        return [
            'display' => $this->resource->display(),
            'section' => $this->resource->section(),
            'id' => $this->resource->id(),
            'url' => $this->resource->editableUrl(),
            'icon' => $this->resource->icon(),
            'manipulations' => $this->resource->manipulations(),
            'children' => $children ?? [],
            'original' => NavItem::make($this->resource->original()),
        ];
    }
}
