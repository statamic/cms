<?php

namespace Statamic\Entries;

use Facades\Statamic\View\Cascade;
use Illuminate\Support\Facades\Storage;
use Statamic\Contracts\Entries\LivePreview as LivePreviewContract;

class LivePreview implements LivePreviewContract
{
    public function toLivePreviewResponse($entry, $request, $extras)
    {
        Cascade::set('live_preview', $extras);

        if (config('statamic.live_preview.external_url')) {
            $livePreviewCache = Storage::disk('local')->exists('live-preview-cache.json') ?
            json_decode(Storage::disk('local')->get('live-preview-cache.json'), true) :
            [];

            $livePreviewCache[auth()->user()->id()][$entry->id()] = $entry->supplements();

            Storage::disk('local')->put('live-preview-cache.json', json_encode($livePreviewCache));

            $iframeSrc = config('statamic.live_preview.external_url').'/'.$request->path().'?preview='.auth()->user()->id();

            return response('<iframe frameBorder="0"
                style="width: 100%; height: 100%"
                src="'.$iframeSrc.'"></iframe>');
        }

        return $entry->toResponse($request);
    }
}
