<?php

namespace Statamic\Http\Controllers\CP\Forms;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Contracts\Forms\Form;
use Statamic\Facades\Fieldset as FieldsetRepository;
use Statamic\Forms\Fields\FormField;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;
use Statamic\Support\Str;

use function Statamic\trans as __;

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

                        foreach ($formFields->getImportedFields($config) as $formField) {
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
            'hidden' => $fieldConfig['hidden'] ?? false,
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

        [$fieldConfigs, $importConfigs] = $this->existingConfigs($form->formFields()->pages());

        $fieldsByPage = collect($request->input('fields', []))->groupBy('page_index');

        $pages = $form->formFields()->pages()->map(function (array $page, int $pageIndex) use ($pageRules, $fieldsByPage, $fieldConfigs, $importConfigs): array {
            $pageId = $page['id'] ?? null;

            if ($pageId && $pageRules->has($pageId)) {
                $page['rules'] = $pageRules->get($pageId);
            }

            $page['sections'] = $this->rebuildSections(
                $fieldsByPage->get($pageIndex, collect()),
                $page['sections'] ?? [],
                $fieldConfigs,
                $importConfigs,
            );

            return $page;
        })->all();

        $form->formFields(['pages' => $pages]);
    }

    private function existingConfigs($pages): array
    {
        $fields = [];
        $imports = [];

        foreach ($pages as $page) {
            foreach ($page['sections'] ?? [] as $section) {
                foreach ($section['fields'] ?? [] as $fieldConfig) {
                    if (isset($fieldConfig['import'])) {
                        $imports[$fieldConfig['import']] = $fieldConfig;
                    } elseif (isset($fieldConfig['handle'])) {
                        $fields[$fieldConfig['handle']] = $fieldConfig;
                    }
                }
            }
        }

        return [$fields, $imports];
    }

    // Rebuild a page's sections from the (reordered) fields sent by the tree, reusing
    // the existing field/import/section configs so only order and logic conditions change.
    private function rebuildSections($fields, array $existingSections, array $fieldConfigs, array $importConfigs): array
    {
        $sections = [];
        $sectionIndex = -1;
        $lastImport = null;

        foreach ($fields as $field) {
            if (($field['section_start'] ?? false) || empty($sections)) {
                $sectionIndex++;

                $section = $existingSections[$sectionIndex] ?? [];
                $section['display'] = $field['section_display'] ?? ($section['display'] ?? __('Section'));
                $section['fields'] = [];

                $sections[] = $section;
                $lastImport = null;
            }

            $current = count($sections) - 1;

            // A fieldset import expands into several fields; collapse them back into
            // the single import entry it came from.
            if ($import = ($field['import'] ?? null)) {
                if ($import !== $lastImport && isset($importConfigs[$import])) {
                    $sections[$current]['fields'][] = $importConfigs[$import];
                }

                $lastImport = $import;

                continue;
            }

            $lastImport = null;

            if ($config = $fieldConfigs[$field['handle'] ?? null] ?? null) {
                $sections[$current]['fields'][] = $this->applyFieldConditions($config, $field);
            }
        }

        return $sections;
    }

    private function applyFieldConditions(array $fieldConfig, array $conditions): array
    {
        // Inline fields store logic inside `field`; reference fields (where `field`
        // is a string handle) store it as overrides under `config`.
        $key = is_array($fieldConfig['field'] ?? null) ? 'field' : 'config';
        $target = $fieldConfig[$key] ?? [];

        unset($target['hidden'], $target['if'], $target['unless'], $target['if_any'], $target['unless_any'], $target['always_save']);

        foreach (['hidden', 'if', 'unless', 'if_any', 'unless_any', 'always_save'] as $logicKey) {
            if (! empty($conditions[$logicKey])) {
                $target[$logicKey] = $conditions[$logicKey];
            }
        }

        if ($key === 'config' && empty($target)) {
            unset($fieldConfig['config']);
        } else {
            $fieldConfig[$key] = $target;
        }

        return $fieldConfig;
    }
}
