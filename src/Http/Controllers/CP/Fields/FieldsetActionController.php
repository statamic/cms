<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Statamic\Facades\Fieldset;
use Statamic\Http\Controllers\CP\ActionController;

class FieldsetActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Fieldset::find($item);
        });
    }
}
