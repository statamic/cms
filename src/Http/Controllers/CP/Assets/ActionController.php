<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\Facades\Asset;
use Statamic\Http\Controllers\CP\ActionController as Controller;

class ActionController extends Controller
{
    protected static $key = 'asset-browser';

    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Asset::find($item);
        });
    }
}
