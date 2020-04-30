<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\Assets\AssetFolder;
use Statamic\Http\Controllers\CP\ActionController as Controller;

class FolderActionController extends Controller
{
    protected static $key = 'asset-folders';

    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($path) use ($context) {
            return AssetFolder::find("{$context['container']}::{$path}");
        });
    }
}
