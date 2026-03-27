<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Inertia\Inertia;
use Statamic\Http\Controllers\CP\CpController;

class FormConnectController extends CpController
{
    public function __invoke($form)
    {
        return Inertia::render('forms/Connect', [
            'form' => $form,
        ]);
    }
}
