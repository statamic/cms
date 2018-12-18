<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Entry;
use Statamic\API\Search;
use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\Fields\Validation;
use Statamic\CP\Publish\ProcessesFields;
use Illuminate\Http\Resources\Json\Resource;

class EntriesController extends CpController
{
    use ProcessesFields;

    public function index($collection)
    {
        $collection = Collection::whereHandle($collection);

        $entries = $this
            ->indexQuery($collection)
            ->orderBy($sort = request('sort', 'title'), request('order', 'asc'))
            ->paginate();

        $entries->setCollection($entries->getCollection()->supplement(function ($entry) {
            return ['deleteable' => me()->can('delete', $entry)];
        }));

        return Resource::collection($entries)->additional(['meta' => [
            'sortColumn' => $sort,
            'columns' => [
                ['label' => __('Title'), 'field' => 'title'],
                ['label' => __('Slug'), 'field' => 'slug'],
            ],
        ]]);
    }

    protected function indexQuery($collection)
    {
        $query = $collection->queryEntries();

        if ($search = request('search')) {
            if ($collection->hasSearchIndex()) {
                return $collection->searchIndex()->ensureExists()->search($search);
            }

            $query->where('title', 'like', '%'.$search.'%');
        }

        return $query;
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
            'title' => $entry->get('title'),
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

        // TODD: Localization

        return response('', 204);
    }

    public function create()
    {
        return view('statamic::entries.create');
    }

    public function store()
    {

    }

    public function destroy($collection, $entry)
    {
        if (! $entry = Entry::find($entry)) {
            return $this->pageNotFound();
        }

        $this->authorize('delete', $entry);

        $entry->delete();

        return response('', 204);
    }
}
