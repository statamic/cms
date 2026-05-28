<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Facades\Statamic\Fields\FieldtypeRepository;
use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Statamic\Contracts\Forms\Form;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Fieldset;
use Statamic\Fields\Field;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFields;
use Statamic\Forms\Fields\FormFieldTransformer;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fieldtypes\Fallback;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Controllers\CP\Fields\ManagesFields;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use function Statamic\trans as __;

class FormBuilderController extends CpController
{
    use ManagesFields;

    public function edit($form)
    {
        $this->authorize('edit', $form);

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

        $fieldtypes = $formFieldtypes->merge($legacySelectableFieldtypes)->sortBy->title()->values();

        return Inertia::render('forms/Builder', [
            'form' => $form,
            'initialFormFields' => $this->toVueObject($form->formFields()),
            'formsProInstalled' => Statamic::formsProInstalled(),
            'fieldtypes' => $fieldtypes->map(fn (FormFieldtype $fieldtype): array => [
                ...$fieldtype->toArray(),
                'preview' => $this->fieldtypePreview($fieldtype),
                'example' => $this->fieldtypeExample($fieldtype),
            ]),
            'action' => cp_route('forms.builder.update', $form->handle()),
            ...$this->fieldProps(),
        ]);
    }

    public function update(Request $request, $form)
    {
        $this->authorize('edit', $form);

        $request->validate([
            'pages' => ['required', 'array', 'min:1'],
            'pages.*.sections' => ['required', 'array', 'min:1'],
        ]);

        $this->validateFields($request);

        $this->validateUniqueHandles($request);

        $this->setFormFields($request, $form);

        $form->save();
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

    private function toVueObject(FormFields $formFields): array
    {
        return [
            'pages' => $formFields->pages()->map(function (array $page): array {
                return array_merge($this->pageToVue($page), ['_id' => $page['id'] ?? Str::random(8)]);
            })->values()->all(),
        ];
    }

    private function pageToVue(array $page): array
    {
        return [
            'display' => $page['display'] ?? null,
            'instructions' => $page['instructions'] ?? null,
            'button_label' => $page['button_label'] ?? null,
            'previous_page_label' => $page['previous_page_label'] ?? null,
            'rules' => collect($page['rules'] ?? [])->map(function (array $rule): array {
                return [
                    '_id' => Str::random(),
                    'conditions' => collect($rule['conditions'] ?? [])->map(function (array $condition): array {
                        return array_merge($condition, ['_id' => Str::random()]);
                    })->values()->all(),
                    'destination' => $rule['destination'] ?? null,
                ];
            })->values()->all(),
            'sections' => collect($page['sections'])->map(function (array $section): array {
                return array_merge($this->sectionToVue($section), ['_id' => Str::random()]);
            })->values()->all(),
        ];
    }

    private function sectionToVue(array $section): array
    {
        return Arr::removeNullValues([
            'display' => $section['display'] ?? __('Section'),
        ]) + [
            'fields' => collect($section['fields'] ?? [])->map(function (array $field) {
                return array_merge(FormFieldTransformer::toVue($field), ['_id' => Str::random()]);
            })->values()->all(),
        ];
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
                        ->addValues($field['config'] ?? []);

                    try {
                        $fields->validate();
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
                            'fields' => [
                                ...FormField::commonFieldOptions()->items(),
                                ...$blueprint->contents()['tabs']['main']['sections'][0]['fields'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function setFormFields(Request $request, Form $form): Form
    {
        $pages = collect($request->pages)->map(function (array $page) {
            return Arr::removeNullValues([
                'id' => $page['_id'],
                'display' => $page['display'] ?? null,
                'instructions' => $page['instructions'] ?? null,
                'button_label' => $page['button_label'] ?? null,
                'previous_page_label' => $page['previous_page_label'] ?? null,
                'rules' => collect($page['rules'] ?? [])
                    ->map(fn (array $rule) => Arr::only($rule, ['conditions', 'destination']))
                    ->map(function (array $rule) {
                        $rule['conditions'] = collect($rule['conditions'] ?? [])
                            ->map(fn (array $condition) => Arr::only($condition, ['join', 'field', 'operator', 'value']))
                            ->filter(fn (array $condition) => $condition['field'] && $condition['value'])
                            ->values()
                            ->all();

                        return $rule;
                    })
                    ->reject(fn (array $rule) => empty($rule['conditions']) || empty($rule['destination']))
                    ->values()
                    ->all(),
                'sections' => collect($page['sections'])->map(function (array $section) {
                    return Arr::removeNullValues([
                        'display' => $section['display'] ?? null,
                        'fields' => collect($section['fields'])
                            ->reject(fn (array $field) => $field['type'] === 'link_fields')
                            ->map(fn (array $field) => FormFieldTransformer::fromVue($field))
                            ->all(),
                    ]);
                })->all(),
            ]);
        })->all();

        $form->formFields(['pages' => $pages]);

        return $form;
    }
}
