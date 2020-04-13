<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\Http\Controllers\CP\ActionController;

class SubmissionActionController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        $form = $this->request->route('form');

        return $items->map(function ($item) use ($form) {
            return $form->submission($item);
        });
    }
}
