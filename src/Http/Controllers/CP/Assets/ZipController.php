<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\Facades\Asset;
use Statamic\Http\Controllers\Controller;
use Statamic\Support\Str;
use STS\ZipStream\ZipStreamFacade as Zip;

class ZipController extends Controller
{
    /**
     * Stream the ZIP.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($encodedAssetIds)
    {
        $encodedAssetIds = Str::split($encodedAssetIds, ',');

        $paths = collect($encodedAssetIds)
            ->map(function ($encodedAssetId) {
                return $this->asset($encodedAssetId);
            })
            ->map(function ($asset) {
                return $asset->resolvedPath();
            })
            ->all();

        return Zip::create('assets.zip', $paths);
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
