<?php

namespace Statamic\Http\Controllers\CP;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ClearCacheController extends CpController
{
    public function index()
    {
        return view('statamic::utilities.clear-cache');
    }

    public function clear(Request $request)
    {
        $caches = collect($request->validate([
            'caches' => 'required',
        ])['caches']);

        if ($caches->contains('cache')) {
            Artisan::call('cache:clear');
        }

        if ($caches->contains('stache')) {
            Artisan::call('statamic:stache:clear');
        }

        if ($caches->contains('static')) {
            Artisan::call('statamic:static:clear');
        }

        if ($caches->contains('glide')) {
            Artisan::call('statamic:glide:clear');
        }

        return back()->withSuccess('Cache successfully cleared.');
    }
}
