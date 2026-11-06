<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Statamic\Http\Controllers\CP\CpController;

/** @deprecated */
class FormBlueprintController extends CpController
{
    public function __invoke($form)
    {
        return redirect(cp_route('forms.show', $form->handle()));
    }
}
