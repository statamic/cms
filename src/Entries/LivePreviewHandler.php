<?php

namespace Statamic\Entries;

use Facades\Statamic\View\Cascade;
use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Entries\LivePreviewHandler as LivePreviewContract;
use Statamic\Support\Str;

class LivePreviewHandler implements LivePreviewContract
{
    public function toLivePreviewResponse($entry, $request, $extras)
    {
        Cascade::set('live_preview', $extras);

        if (config('statamic.live_preview.external_url')) {
            $livePreviewCache = Cache::get('live-preview-data', []);

            if ($entry->id()) {
                $data = $entry->supplements();
            } else {
                $data = $entry->data();
            }

            $url = $entry->url();
            if ($url === '/') {
                $currentLivePreviewUrl = Cache::get('current-live-preview-url', []);
                if (isset($currentLivePreviewUrl[auth()->user()->id()])) {
                    $url = $currentLivePreviewUrl[auth()->user()->id()];
                } else {
                    $url = '/'.Str::random(10);
                    $currentLivePreviewUrl[auth()->user()->id()] = $url;
                    Cache::put('current-live-preview-url', $currentLivePreviewUrl);
                }
            }

            $livePreviewCache[auth()->user()->id()][$url] = [
                'data' => $data,
                'collection' => $entry->collection()->handle(),
                'blueprint' => $entry->blueprint()->handle(),
                'slug' => $entry->slug(),
            ];

            Cache::put('live-preview-data', $livePreviewCache, now()->addMinutes(5));

            $livePreviewUrl = config('statamic.live_preview.external_url').$url.'?preview='.auth()->user()->id();

            return response([
                'data' => $livePreviewUrl,
            ]);
        }

        return $entry->toResponse($request);
    }
}
