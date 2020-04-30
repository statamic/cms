<?php

namespace Statamic\Http\Controllers\CP\Taxonomies;

use Statamic\Facades\Term;
use Statamic\Http\Controllers\CP\ActionController;

class TermActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return $items->map(function ($item) {
            return Term::find($item);
        })->filter();
    }
}
