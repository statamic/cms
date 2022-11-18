<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Illuminate\Http\Request;
use Statamic\Facades;
use Statamic\Fields\Fieldset;
use Statamic\Fields\FieldTransformer;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class FieldsetController extends CpController
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class.':configure fields');
    }

    public function index(Request $request)
    {
        $fieldsets = Facades\Fieldset::all()
            ->filter(fn ($fieldset) => $request->user()->can('edit', $fieldset))
            ->mapToGroups(function (Fieldset $fieldset) {
                return [
                    $this->groupKey($fieldset) => [
                        'handle' => $fieldset->handle(),
                        'id' => $fieldset->handle(),
                        'delete_url' => $fieldset->deleteUrl(),
                        'edit_url' => $fieldset->editUrl(),
                        'fields' => $fieldset->fields()->all()->count(),
                        'is_deletable' => $fieldset->isDeletable(),
                        'title' => $fieldset->title(),
                    ],
                ];
            });

        if ($request->wantsJson()) {
            return $fieldsets;
        }

        return view('statamic::fieldsets.index', compact('fieldsets'));
    }

    public function edit($fieldset)
    {
        $fieldset = Facades\Fieldset::find($fieldset);

        $vue = [
            'title' => $fieldset->title(),
            'handle' => $fieldset->handle(),
            'fields' => collect(Arr::get($fieldset->contents(), 'fields'))->map(function ($field, $i) {
                return array_merge(FieldTransformer::toVue($field), ['_id' => $i]);
            })->all(),
        ];

        return view('statamic::fieldsets.edit', [
            'fieldset' => $fieldset,
            'fieldsetVueObject' => $vue,
        ]);
    }

    public function update(Request $request, $fieldset)
    {
        $fieldset = Facades\Fieldset::find($fieldset);

        $request->validate([
            'title' => 'required',
            'fields' => 'array',
        ]);

        $fieldset->setContents(array_merge($fieldset->contents(), [
            'title' => $request->title,
            'fields' => collect($request->fields)->map(function ($field) {
                return FieldTransformer::fromVue($field);
            })->all(),
        ]))->save();

        return response('', 204);
    }

    public function create()
    {
        return view('statamic::fieldsets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'handle' => 'required|alpha_dash',
        ]);

        if (Facades\Fieldset::find($request->handle)) {
            return back()->withInput()->with('error', __('A fieldset with that name already exists.'));
        }

        $fieldset = (new Fieldset)
            ->setHandle($request->handle)
            ->setContents([
                'title' => $request->title,
                'fields' => [],
            ])->save();

        session()->flash('success', __('Fieldset created'));

        return ['redirect' => $fieldset->editUrl()];
    }

    public function destroy($fieldset)
    {
        $fieldset = Facades\Fieldset::find($fieldset);

        $this->authorize('delete', $fieldset);

        $fieldset->delete();

        return response('');
    }

    private function groupKey(Fieldset $fieldset): string
    {
        return $fieldset->isNamespaced() ? Str::of($fieldset->namespace())->replace('_', ' ')->title() : __('My Fieldsets');
    }
}
