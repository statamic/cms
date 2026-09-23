<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Facades\Statamic\Fields\FieldtypeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\Contracts\Forms\Form;
use Statamic\Forms\Fields\FormFields;
use Statamic\Forms\Fields\FormFieldTransformer;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fieldtypes\Fallback;
use Statamic\Http\Controllers\CP\Fields\ManagesFields;
use Statamic\Support\Arr;
use Statamic\Support\Str;

use function Statamic\trans as __;

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
            'show_previous_button' => $page['show_previous_button'] ?? filled($page['previous_page_label'] ?? null),
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

    private function setFormFields(Request $request, Form $form): Form
    {
        $pages = collect($request->pages)->map(function (array $page) {
            return Arr::removeNullValues([
                'id' => $page['_id'],
                'display' => $page['display'] ?? null,
                'instructions' => $page['instructions'] ?? null,
                'button_label' => $page['button_label'] ?? null,
                'show_previous_button' => $page['show_previous_button'] ?? false,
                'previous_page_label' => $page['previous_page_label'] ?? null,
                'rules' => collect($page['rules'] ?? [])
                    ->map(fn (array $rule) => Arr::only($rule, ['conditions', 'destination']))
                    ->map(function (array $rule) {
                        $rule['conditions'] = collect($rule['conditions'] ?? [])
                            ->map(fn (array $condition) => Arr::only($condition, ['join', 'field', 'operator', 'value']))
                            ->filter(fn (array $condition) => $condition['field'] && $condition['value'] !== null && $condition['value'] !== '')
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
