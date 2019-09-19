<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\Facades\URL;
use Statamic\Facades\Entry;
use Statamic\Facades\Action;
use Illuminate\Http\Request;
use Statamic\Facades\Preference;
use Statamic\Http\Controllers\CP\CpController;

class FavoritesController extends CpController
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'url'  => 'required'
        ]);

        $request->user()->appendPreference('favorites', [
            'name' => request()->name,
            'url' => URL::makeRelative(request()->url)
        ])->save();
    }
}
