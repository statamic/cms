<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\ActionController;

class EntryActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Entry::find($item);
        });
    }
}
