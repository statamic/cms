<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SearchedAssetsCollection extends ResourceCollection
{
    public $collects = FolderAsset::class;

    public function toArray($request)
    {
        return [
            'assets' => $this->collection,
        ];
    }
}
