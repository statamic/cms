<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Entry;
use Illuminate\Http\Request;

class EntriesController extends CpController
{
    public function index($collection)
    {
        // TODO: Bring over the rest of the logic.
        return Entry::whereCollection($collection)->toArray();
    }

    public function edit(Request $request, $collection, $slug)
    {
        $entry = Entry::findBySlug($slug, $collection);

        $this->authorize('view', $entry);

        $tempFieldset = $entry->fieldset()->toPublishArray();
        $tempFieldset['sections'] = collect($tempFieldset['sections'])->map(function ($section, $handle) {
            $section['handle'] = $handle;
            $section['fields'] = collect($section['fields'])->map(function ($config, $handle) {
                $config['handle'] = $handle;
                return $config;
            })->values()->all();
            return $section;
        })->values()->all();

        return view('statamic::entries.edit', [
            'entry' => $entry,
            'tempFieldset' => $tempFieldset,
            'readOnly' => $request->user()->cant('edit', $entry)
        ]);
    }

    public function update(Request $request, $collection, $slug)
    {
        $entry = Entry::findBySlug($slug, $collection);

        $fieldsetFields = $entry->fieldset()->inlinedFields();
        $fields = array_keys($fieldsetFields);
        $extra = ['slug'];
        $validatable = array_merge($fields, $extra);

        $fieldsetValidationRules = collect($fieldsetFields)->map(function ($field) {
            return array_get($field, 'validate', '');
        });

        $rules = $fieldsetValidationRules->merge([
            'slug' => 'required',
        ]);

        $data = $request->validate($rules->all());

        foreach (array_only($data, array_keys($fieldsetFields)) as $key => $value) {
            $entry->set($key, $value);
        }
        $entry->slug($data['slug']);
        $entry->save();

        return ['success' => true];
    }

    public function create()
    {
        return view('statamic::entries.create');
    }

    public function store()
    {

    }

    public function destroy($slug)
    {

    }
}
