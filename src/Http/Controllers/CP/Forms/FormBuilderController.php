<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Facades\Statamic\Fields\FieldtypeRepository;
use Inertia\Inertia;
use Statamic\Fields\Field;
use Statamic\Forms\Fields\Fallback;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesFields;
use Statamic\Support\Arr;

class FormBuilderController extends CpController
{
    use ManagesFields;

    public function __invoke($form)
    {
        $this->authorize('edit', $form);

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

        $fieldtypes = $formFieldtypes->merge($legacySelectableFieldtypes)->sortBy->title()->values();

        return Inertia::render('forms/Builder', [
            'form' => $form,
            'fieldtypes' => $fieldtypes->map(fn (FormFieldtype $fieldtype): array => [
                ...$fieldtype->toArray(),
                'preview' => $this->fieldtypePreview($fieldtype),
                'example' => $this->fieldtypeExample($fieldtype),
            ]),
            ...$this->fieldProps(),
        ]);
    }

    private function fieldtypePreview(FormFieldtype $fieldtype): ?array
    {
        try {
            $field = $fieldtype instanceof Fallback
                ? new Field($fieldtype->toArray()['handle'], ['type' => $fieldtype->toArray()['handle']])
                : (clone $fieldtype)->setField(new FormField($fieldtype->handle(), ['type' => $fieldtype->handle()]))->toField();

            $field->setValue($field->defaultValue());

            return [
                'config' => $field->toPublishArray(),
                'value' => $field->fieldtype()->preProcess($field->value()),
                'meta' => $field->fieldtype()->preload(),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fieldtypeExample(FormFieldtype $fieldtype): ?array
    {
        if (! $example = $fieldtype->example()) {
            return null;
        }

        $config = [
            'type' => $fieldtype->handle(),
            ...Arr::get($example, 'config', []),
        ];

        $field = $fieldtype instanceof Fallback
            ? new Field($fieldtype->toArray()['handle'], $config)
            : (clone $fieldtype)->setField(new FormField($fieldtype->handle(), $config))->toField();

        $field->setValue(Arr::get($example, 'value'));

        return [
            'config' => $field->toPublishArray(),
            'value' => $field->fieldtype()->preProcess($field->value()),
            'meta' => $field->fieldtype()->preload(),
        ];
    }
}
