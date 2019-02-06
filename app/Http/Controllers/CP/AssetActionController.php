<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Asset;

class AssetActionController extends EntryActionController
{
    protected function getSelectedItems($items)
    {
        return $items->map(function ($item) {
            return Asset::find($item);
        });
    }
}
