<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\Http\Resources\CP\Assets\Asset;

class SearchedAssetsCollection extends ResourceCollection
{
    public $collects = Asset::class;

    public function toArray($request)
    {
        return [
            'assets' => $this->collection,
        ];
    }
}