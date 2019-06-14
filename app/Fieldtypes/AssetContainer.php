<?php

namespace Statamic\Fieldtypes;

use Statamic\API;

class AssetContainer extends Relationship
{
    protected $statusIcons = false;
    protected $canEdit = false;
    protected $canCreate = false;

    protected function toItemArray($id, $site = null)
    {
        if ($container = API\AssetContainer::find($id)) {
            return [
                'title' => $container->title(),
                'id' => $container->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return API\AssetContainer::all()->map(function ($container) {
            return [
                'id' => $container->handle(),
                'title' => $container->title(),
            ];
        })->values();
    }

    public function augmentValue($value)
    {
        return API\AssetContainer::find($value);
    }
}
