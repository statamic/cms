<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\Facades\Form;
use Statamic\Http\Controllers\CP\ActionController as Controller;

class ActionController extends Controller
{
    protected static $key = 'forms';

    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Form::find($item);
        });
    }
}
