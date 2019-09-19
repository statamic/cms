<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class FieldtypesController extends CpController
{
    public function index(Request $request)
    {
        $fieldtypes = app('statamic.fieldtypes')
            ->unique() // Remove any dupes in the case of aliases. Aliases are defined later so they will win.
            ->map(function ($class) {
                return app($class)->toArray();
            })
            // ->dd()
            ->sortBy('handle');

        if ($request->selectable) {
            $fieldtypes = $fieldtypes->filter->selectable;
        }

        // TODO: Make sure the configs get preprocessed.

        return $fieldtypes->values();
    }
}
