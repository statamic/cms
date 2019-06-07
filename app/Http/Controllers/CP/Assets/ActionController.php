<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\API\Asset;
use Statamic\Http\Controllers\CP\ActionController as Controller;

class ActionController extends Controller
{
    protected static $key = 'asset-browser';

    protected function getSelectedItems($items)
    {
        return $items->map(function ($item) {
            return Asset::find($item);
        });
    }
}
