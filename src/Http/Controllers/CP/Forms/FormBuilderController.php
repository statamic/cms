<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Fieldset;
use Statamic\Fields\Field;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fieldtypes\Fallback;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Forms\Concerns\ProvidesFormAbilities;
use Statamic\Statamic;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class FormBuilderController extends CpController
{
    use ManagesFormFields, ProvidesFormAbilities;

    public function edit($form)
    {
        $this->authorize('editFields', $form);

        return Inertia::render('forms/Builder', [
            ...$this->fieldProps(),
            'form' => $form,
            'can' => $this->formAbilities($form),
            'initialFormFields' => $this->toVueObject($form->formFields()),
            'formsProInstalled' => Statamic::formsProInstalled(),
            'fieldtypes' => $this->fieldtypes()->map(fn (FormFieldtype $fieldtype): array => [
                ...$fieldtype->toArray(),
                'preview' => $this->fieldtypePreview($fieldtype),
                'example' => $this->fieldtypeExample($fieldtype),
            ]),
            'action' => cp_route('forms.builder.update', $form->handle()),
        ]);
    }

    public function update(Request $request, $form)
    {
        $this->authorize('editFields', $form);

        $request->validate([
            'pages' => ['required', 'array', 'min:1'],
            'pages.*.sections' => ['required', 'array', 'min:1'],
        ]);

        $this->validateFields($request);

        $this->validateUniqueHandles($request);

        $this->setFormFields($request, $form);

        $form->save();
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

    private function validateFields(Request $request): void
    {
        $errors = [];

        foreach ($request->pages as $page) {
            foreach ($page['sections'] ?? [] as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    if (in_array($field['type'], ['import', 'link_fields'])) {
                        continue;
                    }

                    $fieldtype = FormFieldtypeRepository::find($field['fieldtype']);
                    $blueprint = $this->configBlueprint($fieldtype->configBlueprint());

                    $fields = $blueprint
                        ->fields()
                        ->addValues([
                            ...$field['config'] ?? [],
                            'handle' => $field['handle'] ?? null,
                        ])
                        ->preProcess();

                    try {
                        $fields->validate([], ['handle.not_in' => __('statamic::validation.reserved')]);
                    } catch (ValidationException $e) {
                        foreach ($e->errors() as $handle => $messages) {
                            $errors["{$field['_id']}.{$handle}"] = $messages;
                        }
                    }
                }
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function validateUniqueHandles(Request $request): void
    {
        $errors = [];
        $handleToIds = [];

        foreach ($request->pages as $page) {
            foreach ($page['sections'] ?? [] as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    if ($field['type'] === 'link_fields') {
                        continue;
                    }

                    if ($field['type'] === 'import') {
                        if ($fieldset = Fieldset::find($field['fieldset'])) {
                            $prefix = $field['prefix'] ?? '';

                            foreach ($fieldset->fields()->all() as $importedField) {
                                $handle = $prefix.$importedField->handle();
                                $handleToIds[$handle][] = $field['_id'];
                            }
                        }

                        continue;
                    }

                    $handleToIds[$field['handle']][] = $field['_id'];
                }
            }
        }

        foreach ($handleToIds as $handle => $ids) {
            if (count($ids) > 1) {
                $message = __('statamic::validation.duplicate_field_handle', ['handle' => $handle]);

                foreach (array_unique($ids) as $id) {
                    $errors["{$id}.handle"] = [$message];
                }
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function configBlueprint($blueprint)
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'fields' => collect(FormField::commonFieldOptions()->items())
                                ->merge($blueprint->contents()['tabs']['main']['sections'][0]['fields'])
                                ->reverse()->unique('handle')->reverse() // Prioritize the duplicate from the fieldtype
                                ->values()
                                ->all(),
                        ],
                    ],
                ],
            ],
        ]);
    }
}
