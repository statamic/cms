<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Asset;
use Illuminate\Http\Request;
use Statamic\Assets\AssetCollection;

class AssetsFieldtypeController extends CpController
{
    public function index(Request $request)
    {
        $assets = new AssetCollection;

        foreach ($request->input('assets', []) as $url) {
            if (! $asset = Asset::find($url)) {
                continue;
            }

            if ($asset->isImage()) {
                $asset->set('thumbnail', $this->thumbnail($asset, 'small'));
                $asset->set('toenail', $this->thumbnail($asset, 'large'));
            }

            $assets->put($url, $asset);
        }

        return $assets->toArray();
    }

    private function thumbnail($asset, $preset = null)
    {
        return cp_route('assets.thumbnails.show', [
            'asset' => base64_encode($asset->id()),
            'size' => $preset
        ]);
    }
}
