<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Facades\Statamic\Forms\Fields\FormFieldtypeRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Contracts\Forms\Form;
use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FormLogicController extends CpController
{
    use ManagesFormFields;

    public function edit($form)
    {
        $this->authorize('edit', $form);

        $formFields = $form->formFields();

        return Inertia::render('forms/Logic', [
            ...$this->fieldProps(),
            'form' => $form,
            'pages' => $this->pagesToVue($formFields->pages()),
            'fields' => $this->fieldsToVue($formFields),
            'action' => cp_route('forms.logic.update', $form->handle()),
        ]);
    }

    public function update(Request $request, $form)
    {
        $this->authorize('edit', $form);

        $this->mergeLogicIntoForm($request, $form);

        $form->save();
    }

    private function pagesToVue($pages): array
    {
        return $pages->map(function (array $page, int $index): array {
            return [
                '_id' => $page['id'] ?? Str::random(8),
                'display' => $page['display'] ?? __('Page :number', ['number' => $index + 1]),
                'rules' => collect($page['rules'] ?? [])->map(function (array $rule): array {
                    return [
                        '_id' => Str::random(),
                        'conditions' => collect($rule['conditions'] ?? [])->map(function (array $condition): array {
                            return array_merge($condition, ['_id' => Str::random()]);
                        })->values()->all(),
                        'destination' => $rule['destination'] ?? null,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    private function fieldsToVue($formFields): array
    {
        $fields = [];

        foreach ($formFields->pages() as $pageIndex => $page) {
            foreach ($page['sections'] ?? [] as $section) {
                $sectionDisplay = $section['display'] ?? __('Section');
                $isFirstFieldInSection = true;

                foreach ($section['fields'] ?? [] as $config) {
                    if (isset($config['import'])) {
                        $fieldset = FieldsetRepository::find($config['import']);

                        foreach ($formFields->importedFields($config) as $formField) {
                            $fields[] = $this->formFieldToVue(
                                $formField,
                                $pageIndex,
                                'inline',
                                importFieldset: $config['import'],
                                importTitle: $fieldset?->title(),
                                sectionDisplay: $sectionDisplay,
                                sectionStart: $isFirstFieldInSection,
                            );

                            $isFirstFieldInSection = false;
                        }

                        continue;
                    }

                    if (! isset($config['handle'])) {
                        continue;
                    }

                    $type = is_string($config['field'] ?? null) ? 'reference' : 'inline';

                    $fields[] = $this->formFieldToVue(
                        $formFields->field($config['handle']),
                        $pageIndex,
                        $type,
                        handle: $config['handle'],
                        sectionDisplay: $sectionDisplay,
                        sectionStart: $isFirstFieldInSection,
                    );

                    $isFirstFieldInSection = false;
                }
            }
        }

        return $fields;
    }

    private function formFieldToVue(
        ?FormField $formField,
        int $pageIndex,
        string $type,
        ?string $importFieldset = null,
        ?string $importTitle = null,
        ?string $handle = null,
        ?string $sectionDisplay = null,
        bool $sectionStart = false,
    ): array {
        $handle = $handle ?? $formField?->handle();
        $fieldConfig = $formField?->config() ?? [];
        $fieldtype = $formField?->fieldtype();

        $result = [
            '_id' => $handle,
            'handle' => $handle,
            'page_index' => $pageIndex,
            'display' => $fieldConfig['display'] ?? $handle,
            'icon' => $fieldtype?->icon() ?? 'generic-field',
            'category' => $fieldtype->categories()[0] ?? 'other',
            'fieldtype' => $fieldConfig['type'] ?? 'short_answer',
            'type' => $type,
            'if' => $fieldConfig['if'] ?? null,
            'unless' => $fieldConfig['unless'] ?? null,
            'if_any' => $fieldConfig['if_any'] ?? null,
            'unless_any' => $fieldConfig['unless_any'] ?? null,
            'always_save' => $fieldConfig['always_save'] ?? false,
        ];

        if ($importFieldset) {
            $result['import'] = $importFieldset;
            $result['import_title'] = $importTitle;
        }

        if ($sectionStart) {
            $result['section_start'] = true;
            $result['section_display'] = $sectionDisplay;
        }

        if (isset($fieldConfig['options'])) {
            $result['options'] = $fieldConfig['options'];
        }

        return $result;
    }

    private function mergeLogicIntoForm(Request $request, Form $form): void
    {
        $pageRules = collect($request->input('pages', []))
            ->keyBy('_id')
            ->map(function (array $page): array {
                return collect($page['rules'] ?? [])
                    ->map(fn (array $rule): array => Arr::only($rule, ['conditions', 'destination']))
                    ->map(function (array $rule): array {
                        $rule['conditions'] = collect($rule['conditions'] ?? [])
                            ->map(fn (array $condition): array => Arr::only($condition, ['join', 'field', 'operator', 'value']))
                            ->filter(fn (array $condition): bool => ! empty($condition['field']) && $condition['value'] !== null)
                            ->values()
                            ->all();

                        return $rule;
                    })
                    ->reject(fn (array $rule): bool => empty($rule['conditions']) || empty($rule['destination']))
                    ->values()
                    ->all();
            });

        $fieldConditions = collect($request->input('fields', []))->keyBy('_id');

        $pages = $form->formFields()->pages()->map(function (array $page) use ($pageRules): array {
            $pageId = $page['id'] ?? null;

            if ($pageId && $pageRules->has($pageId)) {
                $page['rules'] = $pageRules->get($pageId);
            }

            return $page;
        });

        $pages = $pages->map(function (array $page) use ($fieldConditions): array {
            $page['sections'] = collect($page['sections'] ?? [])->map(function (array $section) use ($fieldConditions): array {
                $section['fields'] = collect($section['fields'] ?? [])->map(function (array $fieldConfig) use ($fieldConditions): array {
                    $handle = $fieldConfig['handle'] ?? null;

                    if (! $handle || ! $fieldConditions->has($handle)) {
                        return $fieldConfig;
                    }

                    $conditions = $fieldConditions->get($handle);
                    $field = $fieldConfig['field'] ?? [];

                    unset($field['if'], $field['unless'], $field['if_any'], $field['unless_any'], $field['always_save']);

                    if (! empty($conditions['if'])) {
                        $field['if'] = $conditions['if'];
                    }
                    if (! empty($conditions['unless'])) {
                        $field['unless'] = $conditions['unless'];
                    }
                    if (! empty($conditions['if_any'])) {
                        $field['if_any'] = $conditions['if_any'];
                    }
                    if (! empty($conditions['unless_any'])) {
                        $field['unless_any'] = $conditions['unless_any'];
                    }
                    if (! empty($conditions['always_save'])) {
                        $field['always_save'] = $conditions['always_save'];
                    }

                    $fieldConfig['field'] = $field;

                    return $fieldConfig;
                })->all();

                return $section;
            })->all();

            return $page;
        })->all();

        $form->formFields(['pages' => $pages]);
    }
}
