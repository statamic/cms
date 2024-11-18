<?php

namespace Statamic\Http\Controllers\CP\Assets;

use League\Flysystem\PathTraversalDetected;
use Statamic\Assets\AssetFolder;
use Statamic\Exceptions\ValidationException;
use Statamic\Http\Controllers\CP\ActionController as Controller;

class FolderActionController extends Controller
{
    protected static $key = 'asset-folders';

    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($path) use ($context) {
            try {
                return AssetFolder::find("{$context['container']}::{$path}");
            } catch (PathTraversalDetected $e) {
                throw ValidationException::withMessages(['selections' => $e->getMessage()]);
            }
        });
    }
}
