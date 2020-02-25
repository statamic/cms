<?php

namespace Statamic\Http\Controllers\CP\Fields;

use Statamic\Facades;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Illuminate\Http\Request;
use Statamic\Fields\Fieldset;
use Statamic\Http\Controllers\CP\CpController;

class FieldsetController extends CpController
{
    public function __construct()
    {
        $this->middleware(\Illuminate\Auth\Middleware\Authorize::class . ':configure fields');
    }

    public function index(Request $request)
    {
        $fieldsets = Facades\Fieldset::all()->map(function ($fieldset) {
            return [
                'id' => $fieldset->handle(),
                'handle' => $fieldset->handle(),
                'title' => $fieldset->title(),
                'fields' => $fieldset->fields()->all()->count(),
                'edit_url' => $fieldset->editUrl(),
                'delete_url' => $fieldset->deleteUrl(),
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

        return view('statamic::fieldsets.edit', compact('fieldset'));
    }

    public function update(Request $request, $fieldset)
    {
        $fieldset = Facades\Fieldset::find($fieldset);

        $request->validate([
            'title' => 'required',
            'fields' => 'array',
        ]);

        $this->save($fieldset, $request);

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
        ]);

        $fieldset = (new Fieldset)
            ->setHandle(Str::snake($request->title))
            ->setContents([
                'title' => $request->title,
                'fields' => []
            ])->save();

        return redirect($fieldset->editUrl())->with('message', __('Saved'));
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
            if (Arr::get($field, 'width') === 100) {
                unset($field['width']);
            }
            return [Arr::pull($field, 'handle') => $field];
        })->all();

        $fieldset->setContents([
            'title' => $request->title,
            'fields' => $fields
        ])->save();
    }
}
