<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\API\Entry;
use Statamic\API\Action;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class EntryColumnController extends CpController
{
    public function __invoke(Request $request, $collection)
    {
        $request->validate([
            'columns' => 'required|array|min:1',
        ]);

        $request->user()->setPreference(
            "collections.{$collection}.columns",
            $request->columns
        )->save();
    }
}
