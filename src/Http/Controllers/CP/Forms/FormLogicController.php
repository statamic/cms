<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Forms\Concerns\ProvidesFormAbilities;

class FormLogicController extends CpController
{
    use ManagesFormFields, ProvidesFormAbilities;

    public function edit($form)
    {
        $this->authorize('editFields', $form);

        return Inertia::render('forms/Logic', [
            ...$this->fieldProps(),
            'form' => $form,
            'can' => $this->formAbilities($form),
            'formFields' => $this->toVueObject($form->formFields()),
            'action' => cp_route('forms.logic.update', $form->handle()),
        ]);
    }

    public function update(Request $request, $form)
    {
        $this->authorize('editFields', $form);

        $this->setFormFields($request, $form);

        $form->save();
    }
}
