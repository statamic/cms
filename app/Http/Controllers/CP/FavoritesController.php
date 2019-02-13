<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Entry;
use Statamic\API\Action;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;

class FavoritesController extends CpController
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'url'  => 'required'
        ]);

        // @TODO Need an append to an array method.

        // $request->user()->addPreference(
        //     "favorites", [
        //         'name' => request()->name,
        //         'url' => request()->url,
        //     ]
        // )->save();
    }

}
