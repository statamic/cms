<?php

namespace Statamic\Http\Resources\CP\Nav;

use Illuminate\Http\Resources\Json\JsonResource;

class Nav extends JsonResource
{
    public function toArray($request)
    {
        return collect($this->resource)
            ->map(function ($section) {
                return array_merge($section, [
                    'items' => $section['items']->map(fn ($item) => NavItem::make($item)),
                ]);
            })
            ->all();
    }
}
