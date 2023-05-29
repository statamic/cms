<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\AssetContainer;

class AssetFolder extends Relationship
{
    protected $component = 'asset_folder';
    protected $statusIcons = false;
    protected $canEdit = false;
    protected $canCreate = false;
    protected $selectable = false;

    protected function toItemArray($id, $site = null)
    {
        return ['title' => $id, 'id' => $id];
    }

    public function getIndexItems($request)
    {
        return AssetContainer::find($request->container)
            ->folders()
            ->map(function ($folder) {
                return ['id' => $folder, 'title' => $folder];
            })
            ->prepend(['id' => '/', 'title' => '/'])
            ->when($request->search, function ($folders) use ($request) {
                return $folders->filter(function ($folder) use ($request) {
                    return str_contains($folder['title'], $request->search);
                });
            })
            ->values();
    }
}
