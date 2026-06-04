<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Support\Collection;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fieldtypes\Fallback;
use Statamic\Http\Controllers\CP\Fields\ManagesFields;

trait ManagesFormFields
{
    use ManagesFields {
        fieldProps as getFieldProps;
    }

    private function fieldProps(): array
    {
        return [
            ...$this->getFieldProps(),
            'fieldtypes' => $this->fieldtypes(),
        ];
    }

    private function fieldtypes(): Collection
    {
        $formFieldtypes = app('statamic.form-fieldtypes')
            ->unique()
            ->map(fn ($class) => app($class))
            ->filter->isSelectable()
            ->reject(fn (FormFieldtype $fieldtype) => $this->wrappedFieldtypeIsUnselectable($fieldtype))
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

        return $formFieldtypes->merge($legacySelectableFieldtypes)->sortBy->title()->values();
    }

    private function wrappedFieldtypeIsUnselectable(FormFieldtype $fieldtype): bool
    {
        if (! $handle = $fieldtype::fieldtype()) {
            return false;
        }

        if (! FieldtypeRepository::selectableInFormIsOverriden($handle)) {
            return false;
        }

        return ! FieldtypeRepository::hasBeenMadeSelectableInForms($handle);
    }
}
