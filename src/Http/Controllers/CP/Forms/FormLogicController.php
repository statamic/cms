<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Inertia\Inertia;
use Statamic\Http\Controllers\CP\CpController;

class FormLogicController extends CpController
{
    public function __invoke($form)
    {
        $this->authorize('edit', $form);

        return Inertia::render('forms/Logic', [
            'form' => $form,
        ]);
    }
}
