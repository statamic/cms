<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Fields\Field;
use Statamic\Fields\FieldTransformer;
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

        // todo: validate fields
        $request->validate([
            'pages' => ['required', 'array'],
        ]);

        $pages = collect($request->pages)->map(function (array $page) {
            return Arr::removeNullValues([
                'display' => $page['display'] ?? null,
                'instructions' => $page['instructions'] ?? null,
                'button_label' => $page['button_label'] ?? null,
                'previous_page_label' => $page['previous_page_label'] ?? null,
                'sections' => collect($page['sections'])->map(function (array $section) {
                    return Arr::removeNullValues([
                        'display' => $section['display'] ?? null,
                        'fields' => collect($section['fields'])
                            ->map(fn (array $field) => FormFieldTransformer::fromVue($field))
                            ->all(),
                    ]);
                })->all(),
            ]);
        })->all();

        $form->formFields(['pages' => $pages])->save();
    }

    private function toVueObject(FormFields $formFields): array
    {
        return [
            'pages' => $formFields->pages()->map(function (array $page): array {
                return array_merge($this->pageToVue($page), ['_id' => Str::random()]);
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
            })->all(),
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
}
