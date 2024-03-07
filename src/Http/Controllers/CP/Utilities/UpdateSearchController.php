<?php

namespace Statamic\Http\Controllers\CP\Utilities;

use Illuminate\Http\Request;
use Statamic\Facades\Search;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Str;

class UpdateSearchController extends CpController
{
    public function update(Request $request)
    {
        $indexes = collect($request->validate([
            'indexes' => 'required',
        ])['indexes']);

        $indexes->each(function ($index) {
            [$name, $locale] = explode('::', $index);
            if ($locale) {
                $name = Str::before($name, '_'.$locale);
            }
            Search::index($name, $locale ?: null)->update();
        });

        return back()->withSuccess(__('Update successful.'));
    }
}
