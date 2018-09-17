<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Entry;
use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\Fields\Validation;
use Statamic\CP\Publish\ProcessesFields;

class EntriesController extends CpController
{
    use ProcessesFields;

    public function index($collection)
    {
        // TODO: Bring over the rest of the logic.
        return Entry::whereCollection($collection)->toArray();
    }

    public function edit(Request $request, $collection, $slug)
    {
        $entry = Entry::findBySlug($slug, $collection);

        $this->authorize('view', $entry);

        if (! $blueprint = $entry->blueprint()) {
            throw new \Exception('There is no blueprint defined for this collection.');
        }

        // event(new PublishBlueprintFound($blueprint, 'entry', $entry)); // TODO

        $fields = $blueprint
            ->fields()
            ->addValues($entry->data())
            ->preProcess();

        $values = array_merge($fields->values(), [
            'slug' => $entry->slug()
        ]);

        return view('statamic::entries.edit', [
            'entry' => $entry,
            'values' => $values,
            'readOnly' => $request->user()->cant('edit', $entry)
        ]);
    }

    public function update(Request $request, $collection, $slug)
    {
        $entry = Entry::findBySlug($slug, $collection);

        $this->authorize('edit', $entry);

        $fields = $entry->blueprint()->fields()->addValues($request->all())->process();

        $validation = (new Validation)->fields($fields)->withRules([
            'title' => 'required',
            'slug' => 'required',
        ]);

        $request->validate($validation->rules());

        foreach ($fields->values() as $key => $value) {
            $entry->set($key, $value);
        }

        $entry
            ->set('title', $request->title)
            ->slug($request->slug)
            ->save();

        return response('', 204);
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
