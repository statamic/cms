<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Http\Controllers\CP\CpController;

class FormLogicController extends CpController
{
    use ManagesFormFields;

    public function edit($form)
    {
        $this->authorize('edit', $form);

        return Inertia::render('forms/Logic', [
            ...$this->fieldProps(),
            'form' => $form,
            'formFields' => $this->toVueObject($form->formFields()),
            'action' => cp_route('forms.logic.update', $form->handle()),
        ]);
    }

    public function update(Request $request, $form)
    {
        $this->authorize('edit', $form);

        $this->setFormFields($request, $form);

        $form->save();
    }
}
