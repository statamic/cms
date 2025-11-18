<?php

namespace Statamic\Http\Controllers\CP\Navigation;

use Statamic\Facades\Nav;
use Statamic\Http\Controllers\CP\ActionController;

class NavigationActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Nav::find($item);
        });
    }
}
