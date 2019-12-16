<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\Http\Resources\CP\Assets\Folder;
use Statamic\Http\Resources\CP\Assets\FolderAsset;

class FolderAssetsCollection extends ResourceCollection
{
    public $collects = FolderAsset::class;
    protected $folder;

    public function folder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    public function toArray($request)
    {
        return [
            'assets' => $this->collection,
            'folder' => (new Folder($this->folder))->withChildFolders(),
        ];
    }

    public function with($request)
    {
        return [
            'links' => [
                'asset_actions' => cp_route('assets.actions'),
                'folder_actions' => cp_route('assets.folders.actions', $this->folder->container()->id())
            ]
        ];
    }
}