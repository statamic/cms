<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Statamic\Facades;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Illuminate\Http\Request;
use Statamic\Fields\Blueprint;
use Statamic\Fields\FieldTransformer;
use Statamic\Http\Controllers\CP\CpController;

class BlueprintController extends CpController
{
    public function __construct()
    {
        $this->middleware('can:configure fields');
    }

    public function index()
    {
        $blueprints = Facades\Blueprint::all()->map(function ($blueprint) {
            return [
                'id' => $blueprint->handle(),
                'handle' => $blueprint->handle(),
                'title' => $blueprint->title(),
                'sections' => $blueprint->sections()->count(),
                'fields' => $blueprint->fields()->all()->count(),
                'edit_url' => $blueprint->editUrl(),
                'delete_url' => $blueprint->deleteUrl(),
            ];
        })->values();

        return view('statamic::blueprints.index', compact('blueprints'));
    }

    public function create()
    {
        return view('statamic::blueprints.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required'
        ]);

        $handle = Str::snake($request->title);

        if (Facades\Blueprint::find($handle)) {
            return back()->withInput()->with('error', __('A blueprint with that name already exists.'));
        }

        $blueprint = (new Blueprint)
            ->setHandle($handle)
            ->setContents([
                'title' => $request->title,
                'sections' => [
                    'main' => [
                        'display' => 'Main',
                        'fields' => []
                    ]
                ]
            ])->save();

        return redirect($blueprint->editUrl())->with('success', __('Blueprint created'));
    }

    public function edit($blueprint)
    {
        $blueprint = Facades\Blueprint::find($blueprint);

        return view('statamic::blueprints.edit', [
            'blueprint' => $blueprint,
            'blueprintVueObject' => $this->toVueObject($blueprint)
        ]);
    }

    public function update(Request $request, $blueprint)
    {
        $blueprint = Facades\Blueprint::find($blueprint);

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

    public function destroy($blueprint)
    {
        $blueprint = Facades\Blueprint::find($blueprint);

        $this->authorize('delete', $blueprint);

        $blueprint->delete();

        return response('');
    }
}
