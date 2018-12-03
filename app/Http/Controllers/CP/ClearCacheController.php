<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Stache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Statamic\StaticCaching\Cacher as StaticCacher;

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
            Cache::clear();
        }

        if ($caches->contains('stache')) {
            Stache::clear();
        }

        if ($caches->contains('static')) {
            app(StaticCacher::class)->flush();
        }

        if ($caches->contains('glide')) {
            // TODO: Do we still want to offer clearing glide image cache?
        }

        return back()->withSuccess('Cache successfully cleared.');
    }
}
