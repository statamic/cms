<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Statamic\API\Collection;

class CollectionsFieldtypeController extends RelationshipFieldtypeController
{
    protected function getIndexItems()
    {
        return Collection::all()->map(function ($collection) {
            return [
                'id' => $collection->handle(),
                'title' => $collection->title(),
            ];
        })->values();
    }

    protected function toItemArray($id)
    {
        if ($collection = Collection::whereHandle($id)) {
            return [
                'title' => $collection->title(),
                'id' => $collection->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }
}
