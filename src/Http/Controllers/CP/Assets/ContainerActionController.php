<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Statamic\Facades\AssetContainer;
use Statamic\Http\Controllers\CP\ActionController as Controller;

class ContainerActionController extends Controller
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(fn ($item) => AssetContainer::find($item));
    }
}
