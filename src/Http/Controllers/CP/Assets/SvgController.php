<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\Facades\Asset;
use Statamic\Http\Controllers\Controller;

class SvgController extends Controller
{
    /**
     * Display the SVG.
     *
     * @param  string  $asset
     * @return \Illuminate\Http\Response
     */
    public function show($asset)
    {
        $asset = $this->asset($asset);

        if (! $contents = $asset->disk()->get($asset->path())) {
            abort(500);
        }

        return response($contents)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Get an asset, or throw a 404 if not found.
     *
     * @param  string  $asset  An encoded asset ID from the URL.
     * @return \Statamic\Contracts\Assets\Asset
     */
    private function asset($asset)
    {
        if (! $asset = Asset::find(base64_decode($asset))) {
            abort(404);
        }

        return $asset;
    }
}
