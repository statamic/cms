<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Statamic\API\Collection;
use Statamic\Fields\Fieldtypes\Collections as CollectionsFieldtype;

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

    protected function fieldtype()
    {
        return new CollectionsFieldtype;
    }
}
