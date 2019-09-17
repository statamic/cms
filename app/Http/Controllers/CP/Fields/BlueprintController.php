<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Statamic\Facades;
use Statamic\Support\Arr;
use Illuminate\Http\Request;
use Statamic\Fields\Blueprint;
use Statamic\Fields\FieldTransformer;
use Statamic\Http\Controllers\CP\CpController;

class BlueprintController extends CpController
{
    protected $fieldsetFields;

    public function index()
    {
        $this->authorize('index', Blueprint::class, 'You are not authorized to access blueprints.');

        $blueprints = Facades\Blueprint::all()->map(function ($blueprint) {
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

    public function create()
    {
        $this->authorize('create', Blueprint::class, 'You are not authorized to create blueprints.');

        return view('statamic::blueprints.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Blueprint::class, 'You are not authorized to create blueprints.');

        $request->validate([
            'title' => 'required'
        ]);

        $blueprint = (new Blueprint)
            ->setHandle(snake_case($request->title))
            ->setContents([
                'title' => $request->title,
                'sections' => [
                    'main' => [
                        'display' => 'Main',
                        'fields' => []
                    ]
                ]
            ])->save();

        return redirect($blueprint->editUrl())->with('message', __('Saved'));
    }

    public function edit($blueprint)
    {
        $blueprint = Facades\Blueprint::find($blueprint);

        $this->authorize('edit', $blueprint);

        \Statamic::provideToScript([
            'fieldsets' => $this->fieldsets(),
            'fieldsetFields' => $this->fieldsetFields()
        ]);

        return view('statamic::blueprints.edit', [
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint)
        ]);
    }

    public function update(Request $request, $blueprint)
    {
        $blueprint = Facades\Blueprint::find($blueprint);

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
            return FieldTransformer::fromVue($field);
        })->all();
    }

    private function toVueObject(Blueprint $blueprint): array
    {
        return [
            'title' => $blueprint->title(),
            'handle' => $blueprint->handle(),
            'sections' => $blueprint->sections()->map(function ($section, $i) {
                return array_merge($this->sectionToVue($section), ['_id' => $i]);
            })->values()->all()
        ];
    }

    private function sectionToVue($section): array
    {
        return [
            'handle' => $section->handle(),
            'display' => $section->display(),
            'fields' => collect($section->contents()['fields'])->map(function ($field, $i) {
                return array_merge(FieldTransformer::toVue($field), ['_id' => $i]);
            })->all()
        ];
    }

    private function fieldsets()
    {
        return \Statamic\Facades\Fieldset::all()->mapWithKeys(function ($fieldset) {
            return [$fieldset->handle() => [
                'handle' => $fieldset->handle(),
                'title' => $fieldset->title(),
            ]];
        });
    }

    private function fieldsetFields()
    {
        return $this->fieldsetFields = $this->fieldsetFields ?? collect(\Statamic\Facades\Fieldset::all())->flatMap(function ($fieldset) {
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
