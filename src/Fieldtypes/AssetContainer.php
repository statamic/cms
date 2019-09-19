<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades;

class AssetContainer extends Relationship
{
    protected $selectable = false;
    protected $statusIcons = false;
    protected $canEdit = false;
    protected $canCreate = false;

    protected function toItemArray($id, $site = null)
    {
        if ($container = Facades\AssetContainer::find($id)) {
            return [
                'title' => $container->title(),
                'id' => $container->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return Facades\AssetContainer::all()->map(function ($container) {
            return [
                'id' => $container->handle(),
                'title' => $container->title(),
            ];
        })->values();
    }

    public function augmentValue($value)
    {
        return Facades\AssetContainer::find($value);
    }
}
