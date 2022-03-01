<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\Facades\Asset;
use Statamic\Http\Controllers\Controller;

class PdfController extends Controller
{
    /**
     * Display the PDF.
     *
     * @param  string  $encodedAssetId
     * @return \Illuminate\Http\Response
     */
    public function show($encodedAssetId)
    {
        if (! $contents = $this->asset($encodedAssetId)->contents()) {
            abort(500);
        }

        return response($contents)->header('Content-Type', 'application/pdf');
    }

    /**
     * Get an asset, or throw a 404 if not found.
     *
     * @param  string  $encodedAssetId  An encoded asset ID from the URL.
     * @return \Statamic\Contracts\Assets\Asset
     */
    private function asset($encodedAssetId)
    {
        if (! $asset = Asset::find(base64_decode($encodedAssetId))) {
            abort(404);
        }

        return $asset;
    }
}
