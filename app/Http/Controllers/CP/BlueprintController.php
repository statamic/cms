<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API;
use Illuminate\Http\Request;
use Statamic\Fields\Blueprint;

class BlueprintController extends CpController
{
    protected $fieldsetFields;

    public function index()
    {
        $this->authorize('index', Blueprint::class, 'You are not authorized to access fieldsets.');

        $blueprints = API\Blueprint::all()->map(function ($blueprint) {
            return [
                'id' => $blueprint->handle(),
                'handle' => $blueprint->handle(),
                'title' => $blueprint->title(),
                'sections' => $blueprint->sections()->count(),
                'fields' => $blueprint->fields()->all()->count(),
                'edit_url' => $blueprint->editUrl(),
            ];
        })->values();

        return view('statamic::blueprints.index', compact('blueprints'));
    }

    public function edit($blueprint)
    {
        $blueprint = API\Blueprint::find($blueprint);

        $this->authorize('edit', $blueprint);

        \Statamic::provideToScript(['fieldsetFields' => $this->fieldsetFields()]);

        return view('statamic::blueprints.edit', [
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint)
        ]);
    }

    public function update(Request $request, $blueprint)
    {
        $blueprint = API\Blueprint::find($blueprint);

        $this->authorize('edit', $blueprint);

        $request->validate([
            'title' => 'required',
            'sections' => 'array',
        ]);

        $sections = collect($request->sections)->mapWithKeys(function ($section) {
            return [array_pull($section, 'handle') => [
                'display' => $section['display'],
                'fields' => $this->sectionFields($section['fields'])
            ]];
        })->all();

        $blueprint->setContents([
            'title' => $request->title,
            'sections' => $sections
        ])->save();

        return response('', 204);
    }

    private function sectionFields(array $fields)
    {
        return collect($fields)->map(function ($field) {
            return $this->sectionField($field);
        })->all();
    }

    private function sectionField(array $submitted)
    {
        return ($submitted['type'] === 'inline')
            ? $this->inlineSectionField($submitted)
            : $this->referenceSectionField($submitted);
    }

    private function inlineSectionField(array $submitted)
    {
        return [
            'handle' => $submitted['handle'],
            'field' => array_except($submitted['config'], ['isNew'])
        ];
    }

    private function referenceSectionField(array $submitted)
    {
        return array_filter([
            'handle' => $submitted['handle'],
            'field' => $submitted['field_reference'],
            'config' => array_only($submitted['config'], $submitted['config_overrides'])
        ]);
    }

    private function toVueObject(Blueprint $blueprint): array
    {
        return [
            'title' => $blueprint->title(),
            'handle' => $blueprint->handle(),
            'sections' => $blueprint->sections()->map(function ($section) {
                return $this->sectionToVue($section);
            })->values()->all()
        ];
    }

    private function sectionToVue($section): array
    {
        return [
            'handle' => $section->handle(),
            'display' => $section->display(),
            'fields' => collect($section->contents()['fields'])->map(function ($field) {
                return $this->fieldToVue($field);
            })->all()
        ];
    }

    private function fieldToVue($field): array
    {
        return (is_string($field['field']))
            ? $this->referenceFieldToVue($field)
            : $this->inlineFieldToVue($field);
    }

    private function referenceFieldToVue($field): array
    {
        $fieldsetField = array_get($this->fieldsetFields(), $field['field'], []);

        $mergedConfig = array_merge(
            $fieldsetFieldConfig = array_get($fieldsetField, 'config', []),
            $config = array_get($field, 'config', [])
        );

        return [
            'handle' => $field['handle'],
            'type' => 'reference',
            'field_reference' => $field['field'],
            'config' => $mergedConfig,
            'config_overrides' => array_keys($config),
        ];
    }

    private function inlineFieldToVue($field): array
    {
        return [
            'handle' => $field['handle'],
            'type' => 'inline',
            'config' => $field['field']
        ];
    }

    private function fieldsetFields()
    {
        return $this->fieldsetFields = $this->fieldsetFields ?? collect(\Statamic\API\Fieldset::all())->flatMap(function ($fieldset) {
            return collect($fieldset->fields())->mapWithKeys(function ($field, $handle) use ($fieldset) {
                return [$fieldset->handle().'.'.$field->handle() => array_merge($field->toBlueprintArray(), [
                    'fieldset' => [
                        'handle' => $fieldset->handle(),
                        'title' => $fieldset->title(),
                    ]
                ])];
            });
        })->sortBy('display')->all();
    }
}
