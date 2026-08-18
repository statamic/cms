<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Inertia\Inertia;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Forms\Concerns\ProvidesFormAbilities;

class FormConnectController extends CpController
{
    use ProvidesFormAbilities;

    public function __invoke($form)
    {
        $this->authorize('edit', $form);

        return Inertia::render('forms/Connect', [
            'form' => $form,
            'can' => $this->formAbilities($form),
        ]);
    }
}
