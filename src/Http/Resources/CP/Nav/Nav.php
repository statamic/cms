<?php

namespace Statamic\Http\Resources\CP\Nav;

use Illuminate\Http\Resources\Json\JsonResource;

class Nav extends JsonResource
{
    public function toArray($request)
    {
        return collect($this->resource)
            ->map(function ($items, $section) {
                return [
                    'display' => $section,
                    'items' => $items->map(fn ($item) => NavItem::make($item)),
                ];
            })
            ->all();
    }
}
