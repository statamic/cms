<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Statamic\Facades;
use Statamic\Support\Arr;
use Illuminate\Http\Request;
use Statamic\Fields\Fieldset;
use Statamic\Http\Controllers\CP\CpController;

class FieldsetController extends CpController
{
    public function index(Request $request)
    {
        $this->authorize('index', Fieldset::class, 'You are not authorized to access fieldsets.');

        $fieldsets = Facades\Fieldset::all()->map(function ($fieldset) {
            return [
                'id' => $fieldset->handle(),
                'handle' => $fieldset->handle(),
                'title' => $fieldset->title(),
                'fields' => $fieldset->fields()->count(),
                'edit_url' => $fieldset->editUrl(),
            ];
        })->values();

        if ($request->wantsJson()) {
            return $fieldsets;
        }

        return view('statamic::fieldsets.index', compact('fieldsets'));
    }

    public function edit($fieldset)
    {
        $fieldset = Facades\Fieldset::find($fieldset);

        $this->authorize('edit', $fieldset);

        return view('statamic::fieldsets.edit', compact('fieldset'));
    }

    public function update(Request $request, $fieldset)
    {
        $fieldset = Facades\Fieldset::find($fieldset);

        $this->authorize('edit', $fieldset);

        $request->validate([
            'title' => 'required',
            'fields' => 'array',
        ]);

        $this->save($fieldset, $request);

        return response('', 204);
    }

    public function create()
    {
        $this->authorize('create', Fieldset::class);

        return view('statamic::fieldsets.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Fieldset::class);

        $request->validate([
            'handle' => 'required',
            'title' => 'required',
            'fields' => 'array',
        ]);

        $fieldset = (new Fieldset)->setHandle($request->handle);

        $this->save($fieldset, $request);

        session()->flash('message', __('Saved'));

        return ['redirect' => $fieldset->editUrl()];
    }

    public function destroy($fieldset)
    {
        $fieldset = Facades\Fieldset::find($fieldset);

        $this->authorize('delete', $fieldset);

        $fieldset->delete();

        return response('');
    }

    /**
     * Quickly create a new barebones fieldset from within the fieldtype
     *
     * @return array
     */
    public function quickStore(Request $request)
    {
        $title = $request->title;

        if (Facades\Fieldset::exists($handle = snake_case($title))) {
            return ['success' => true];
        }

        $fieldset = (new Fieldset)->setHandle($handle)->setContents([
            'title' => $request->title,
            'fields' => []
        ])->save();

        return ['success' => true];
    }

    private function save(Fieldset $fieldset, Request $request)
    {
        $fields = collect($request->fields)->mapWithKeys(function ($field) {
            $field = Arr::removeNullValues($field);
            $field = Arr::except($field, ['_id', 'isNew']);
            return [Arr::pull($field, 'handle') => $field];
        })->all();

        $fieldset->setContents([
            'title' => $request->title,
            'fields' => $fields
        ])->save();
    }
}
