<?php

namespace Statamic\Entries;

use Facades\Statamic\View\Cascade;
use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Entries\LivePreviewHandler as LivePreviewContract;

class LivePreviewHandler implements LivePreviewContract
{
    public function toLivePreviewResponse($entry, $request, $extras)
    {
        Cascade::set('live_preview', $extras);

        if (config('statamic.live_preview.external_url')) {
            $livePreviewCache = Cache::get('live-preview-data', []);

            $livePreviewCache[auth()->user()->id()][$entry->id()] = $entry->supplements();

            Cache::put('live-preview-data', $livePreviewCache, now()->addMinutes(5));

            $path = $request->path();
            if (! starts_with($path, '/')) {
                $path = str_start($path, '/');
            }

            $livePreviewUrl = config('statamic.live_preview.external_url').$path.'?preview='.auth()->user()->id();

            return response([
                'data' => $livePreviewUrl,
            ]);
        }

        return $entry->toResponse($request);
    }
}
