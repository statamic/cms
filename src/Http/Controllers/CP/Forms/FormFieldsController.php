<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Inertia\Inertia;
use Statamic\Http\Controllers\CP\CpController;

class FormFieldsController extends CpController
{
    public function __invoke($form)
    {
        return Inertia::render('forms/Fields', [
            'form' => $form,
        ]);
    }
}
