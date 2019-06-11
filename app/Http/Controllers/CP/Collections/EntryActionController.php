<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\API\Entry;
use Statamic\API\Action;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\ActionController;

class EntryActionController extends ActionController
{
    protected static $key = 'entries';

    protected function getSelectedItems($items)
    {
        return $items->map(function ($item) {
            return Entry::find($item);
        });
    }
}
