<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\Facades\Action;
use Statamic\Facades\Asset;
use Statamic\Http\Controllers\CP\ActionController as Controller;
use Statamic\Http\Resources\API\AssetResource;

class ActionController extends Controller
{
    use ExtractsFromAssetFields;

    protected static $key = 'asset-browser';

    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Asset::find($item);
        });
    }

    protected function getItemData($asset, $context): array
    {
        $blueprint = $asset->blueprint();

        [$values] = $this->extractFromFields($asset, $blueprint);

        return array_merge((new AssetResource($asset))->resolve(), [
            'values' => $values,
            'itemActions' => Action::for($asset, $context),
        ]);
    }
}
