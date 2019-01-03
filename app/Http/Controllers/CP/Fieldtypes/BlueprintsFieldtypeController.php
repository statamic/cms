<?php

namespace Statamic\Http\Controllers\CP\Fieldtypes;

use Statamic\API\Blueprint;
use Statamic\Fields\Fieldtypes\Blueprints;

class BlueprintsFieldtypeController extends RelationshipFieldtypeController
{
    protected function getIndexItems()
    {
        return Blueprint::all()->map(function ($blueprint) {
            return [
                'id' => $blueprint->handle(),
                'title' => $blueprint->title(),
            ];
        })->values();
    }

    protected function fieldtype()
    {
        return new Blueprints;
    }
}
