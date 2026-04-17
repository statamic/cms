<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Facades\Statamic\Fields\FieldtypeRepository;
use Inertia\Inertia;
use Statamic\Forms\Fields\Fallback;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Http\Controllers\CP\CpController;

class FormConnectController extends CpController
{
    public function __invoke($form)
    {
        // TODO: Remove from this controller when wiring up the form builder.
        $formFieldtypes = app('statamic.form-fieldtypes')
            ->unique()
            ->map(fn ($class) => app($class))
            ->filter->isSelectable()
            ->values();

        $fieldtypesPortedToFormFieldtypes = $formFieldtypes
            ->map(fn (FormFieldtype $fieldtype) => $fieldtype::fieldtype())
            ->filter()
            ->unique()
            ->values();

        $legacySelectableFieldtypes = FieldtypeRepository::classes()
            ->map(fn ($class) => app($class))
            ->filter->selectableInForms()
            ->reject(fn ($fieldtype) => $fieldtypesPortedToFormFieldtypes->contains($fieldtype->handle()))
            ->map(fn ($fieldtype) => (new Fallback)->wrapping($fieldtype))
            ->values();

        return Inertia::render('forms/Connect', [
            'form' => $form,
            'fieldtypes' => $formFieldtypes->merge($legacySelectableFieldtypes)->sortBy->handle()->values(),
        ]);
    }
}
