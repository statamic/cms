<?php

namespace Statamic\Http\Controllers\CP;

use Statamic\API\Site;
use Statamic\API\Entry;
use Statamic\API\Blueprint;
use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\Fields\Validation;
use Statamic\CP\Publish\ProcessesFields;
use Illuminate\Http\Resources\Json\Resource;
use Statamic\Contracts\Data\Entries\Entry as EntryContract;

class EntriesController extends CpController
{
    use ProcessesFields;

    public function index($collection)
    {
        $collection = Collection::whereHandle($collection);

        $entries = $this
            ->indexQuery($collection)
            ->where('site', Site::selected()->handle())
            ->orderBy($sort = request('sort', 'title'), request('order', 'asc'))
            ->paginate(request('perPage'));

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

    public function edit(Request $request, $collection, $id, $slug, $site)
    {
        if (! Site::get($site)) {
            return $this->pageNotFound();
        }

        if (! $entry = Entry::find($id)) {
            return $this->pageNotFound();
        }

        if (! $entry->collection()->sites()->contains($site)) {
            return $this->pageNotFound();
        }

        $entry = $entry->inOrClone($site);

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

        $viewData = [
            'editing' => true,
            'actions' => [
                'update' => $entry->updateUrl()
            ],
            'values' => $values,
            'meta' => $fields->meta(),
            'collection' => $this->collectionToArray($entry->collection()),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => $request->user()->cant('edit', $entry),
            'localizations' => $entry->collection()->sites()->map(function ($handle) use ($entry) {
                $exists = $entry->entry()->existsIn($handle);
                $localized = $entry->entry()->inOrClone($handle);
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $entry->locale(),
                    'exists' => $exists,
                    'published' => $exists ? $localized->published() : false,
                    'url' => $localized->editUrl(),
                ];
            })->all()
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic::entries.edit', array_merge($viewData, [
            'entry' => $entry
        ]));
    }

    public function update(Request $request, $collection, $id, $slug, $site)
    {
        if (! $entry = Entry::find($id)) {
            return $this->pageNotFound();
        }

        $entry = $entry->inOrClone($site);

        $this->authorize('edit', $entry);

        $fields = $entry->blueprint()->fields()->addValues($request->all())->process();

        $validation = (new Validation)->fields($fields)->withRules([
            'title' => 'required',
            'slug' => 'required',
        ]);

        $request->validate($validation->rules());

        $values = array_except($fields->values(), ['slug']);

        foreach ($values as $key => $value) {
            $entry->set($key, $value);
        }

        $entry
            ->set('title', $request->title)
            ->slug($request->slug)
            ->save();

        return $entry->toArray();
    }

    public function create(Request $request, $collection, $site)
    {
        if (! Site::get($site)) {
            return $this->pageNotFound();
        }

        if (! $collection = Collection::whereHandle($collection)) {
            return $this->pageNotFound();
        }

        $blueprint = $request->blueprint
            ? Blueprint::find($request->blueprint)
            : $collection->entryBlueprint();

        if (! $blueprint) {
            throw new \Exception('A valid blueprint is required.');
        }

        $fields = $blueprint
            ->fields()
            ->preProcess();

        $values = array_merge($fields->values(), [
            'title' => null,
            'slug' => null
        ]);

        $viewData = [
            'actions' => [
                'store' => cp_route('collections.entries.store', [$collection->handle(), $site])
            ],
            'values' => $values,
            'meta' => $fields->meta(),
            'collection' => $this->collectionToArray($collection),
            'blueprint' => $blueprint->toPublishArray(),
            'localizations' => $collection->sites()->map(function ($handle) use ($collection, $site) {
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $site,
                    'exists' => false,
                    'published' => false,
                    'url' => cp_route('collections.entries.create', [$collection->handle(), $handle]),
                ];
            })->all()
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        return view('statamic::entries.create', $viewData);
    }

    public function store(Request $request, $collection, $site)
    {
        $collection = Collection::whereHandle($collection);

        $this->authorize('create', [EntryContract::class, $collection]);

        $fields = Blueprint::find($request->blueprint)->fields()->addValues($request->all())->process();

        $validation = (new Validation)->fields($fields)->withRules([
            'title' => 'required',
            'slug' => 'required',
        ]);

        $request->validate($validation->rules());

        $values = array_except($fields->values(), ['slug']);

        $entry = Entry::create()
            ->collection($collection)
            ->in($site, function ($localized) use ($values, $request) {
                $localized
                    ->slug($request->slug)
                    ->data($values);
            });

        // TODO: Date handling
        // if ($collection->order() === 'date') {
        //     $entry->date($request->date ?? now());
        // }

        $entry->save();

        return [
            'redirect' => $entry->editUrl(),
            'entry' => $entry->toArray()
        ];
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

    // TODO: Change to $collection->toArray()
    protected function collectionToArray($collection)
    {
        return [
            'title' => $collection->title(),
            'url' => cp_route('collections.show', $collection->handle())
        ];
    }
}
