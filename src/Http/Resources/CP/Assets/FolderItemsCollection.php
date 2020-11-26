<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FolderItemsCollection extends ResourceCollection
{
    public $collects = FolderItem::class;
    protected $folder;

    public function folder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    public function toArray($request)
    {
        return [
            'items' => $this->collection->values(),
            'folder' => new Folder($this->folder),
        ];
    }

    public function with($request)
    {
        return [
            'links' => [
                'run_asset_action' => cp_route('assets.actions.run'),
                'bulk_asset_actions' => cp_route('assets.actions.bulk'),
                'run_folder_action' => cp_route('assets.folders.actions.run', $this->folder->container()->id()),
            ],
        ];
    }
}
