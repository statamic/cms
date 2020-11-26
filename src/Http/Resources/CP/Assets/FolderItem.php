<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\JsonResource;
use \Statamic\Assets\Asset as AssetResource;
use \Statamic\Assets\AssetFolder as FolderResource;

class FolderItem extends JsonResource
{
    public function toArray($request)
    {
        if ($this->resource instanceof AssetResource) {
            return array_merge(
                ['itemType' => 'asset'],
                (new FolderAsset($this->resource))->toArray($request)
            );
        } 

        if ($this->resource instanceof FolderResource) {
            return array_merge(
                ['itemType' => 'folder'],
                (new Folder($this->resource))->toArray($request)
            );
        } 
    }

}
