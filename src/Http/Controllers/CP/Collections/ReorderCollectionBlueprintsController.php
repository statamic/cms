<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesBlueprints;

class ReorderCollectionBlueprintsController extends CpController
{
    use ManagesBlueprints;

    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function __invoke(Request $request, $collection)
    {
        foreach ($request->order as $index => $handle) {
            $collection->entryBlueprint($handle)->setOrder($index + 1)->save();
        }
    }
}
