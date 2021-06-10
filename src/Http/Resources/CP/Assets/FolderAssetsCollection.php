<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\ResourceCollection;

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
            'assets' => $this->collection->values(),
            'folder' => (new Folder($this->folder))->withChildFolders(),
        ];
    }

    public function with($request)
    {
        return [
            'links' => [
                'asset_action' => cp_route('assets.actions.run'),
                'folder_action' => cp_route('assets.folders.actions.run', $this->folder->container()->id()),
            ],
        ];
    }
}
