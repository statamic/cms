<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Facades\Collection;
use Statamic\Http\Controllers\CP\ActionController;

class CollectionActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(fn ($item) => Collection::find($item));
    }
}
