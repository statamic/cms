<?php

namespace Statamic\Http\Controllers\CP\Globals;

use Statamic\Facades\GlobalSet;
use Statamic\Http\Controllers\CP\ActionController;

class GlobalSetActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return GlobalSet::find($item);
        });
    }
}
