<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Statamic\API\Site;
use Statamic\API\Entry;
use Statamic\CP\Column;
use Statamic\API\Blueprint;
use Illuminate\Http\Request;
use Statamic\API\Collection;
use Statamic\API\Preference;
use Statamic\Fields\Validation;
use Illuminate\Http\Resources\Json\Resource;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Events\Data\PublishBlueprintFound;
use Statamic\Http\Requests\FilteredSiteRequest;
use Statamic\Contracts\Data\Entries\Entry as EntryContract;

class EntriesController extends CpController
{
    public function index(FilteredSiteRequest $request, $handle)
    {
        $collection = Collection::whereHandle($handle);

        $query = $this->indexQuery($collection);

        $this->filter($query, $request->filters);

        $sortField = request('sort');
        $sortDirection = request('order');

        if (!$sortField && !request('search')) {
            $sortField = $collection->sortField();
            $sortDirection = $collection->sortDirection();
        }

        $paginator = $query
            ->orderBy($sortField, $sortDirection)
            ->paginate(request('perPage'));

        $entries = $paginator->supplement(function ($entry) {
            return ['deleteable' => me()->can('delete', $entry)];
        })->preProcessForIndex();

        if ($collection->order() === 'date') {
            $entries->supplement('date', function ($entry) {
                return $entry->date()->inPreferredFormat();
            });
        }

        $columns = $collection->entryBlueprint()
            ->columns()
            ->setPreferred("collections.{$handle}.columns")
            ->values();

        return Resource::collection($paginator)->additional(['meta' => [
            'filters' => $request->filters,
            'sortColumn' => $sortField,
            'columns' => $columns,
        ]]);
    }

    protected function filter($query, $filters)
    {
        foreach ($filters as $handle => $value) {
            $class = app('statamic.filters')->get($handle);
            $filter = app($class);
            $filter->apply($query, $value);
        }
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

        $entry = $entry->fromWorkingCopy();

        $blueprint = $entry->blueprint();

        event(new PublishBlueprintFound($blueprint, 'entry', $entry));

        $fields = $blueprint
            ->fields()
            ->addValues($entry->data())
            ->preProcess();

        $values = array_merge($fields->values(), [
            'title' => $entry->get('title'),
            'slug' => $entry->slug()
        ]);

        if ($entry->orderType() === 'date') {
            $datetime = substr($entry->date()->toDateTimeString(), 0, 16);
            $datetime = ($entry->hasTime()) ? $datetime : substr($datetime, 0, 10);
            $values['date'] = $datetime;
        }

        $viewData = [
            'editing' => true,
            'actions' => [
                'save' => $entry->updateUrl(),
                'publish' => $entry->publishUrl(),
                'revisions' => $entry->revisionsUrl(),
            ],
            'values' => $values,
            'meta' => $fields->meta(),
            'collection' => $this->collectionToArray($entry->collection()),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => $request->user()->cant('edit', $entry),
            'locale' => $entry->locale(),
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

        $entry = $entry->inOrClone($site)->fromWorkingCopy();

        $this->authorize('edit', $entry);

        $fields = $entry->blueprint()->fields()->addValues($request->all())->process();

        $validation = (new Validation)->fields($fields)->withRules([
            'title' => 'required',
            'slug' => 'required|alpha_dash',
        ]);

        $request->validate($validation->rules());

        $values = array_except($fields->values(), ['slug', 'date']);

        foreach ($values as $key => $value) {
            $entry->set($key, $value);
        }

        $entry
            ->set('title', $request->title)
            ->slug($request->slug);

        if ($entry->orderType() === 'date') {
            // If there's a time, adjust the format into a datetime order string.
            if (strlen($date = $request->date) > 10) {
                $date = str_replace(':', '', $date);
                $date = str_replace(' ', '-', $date);
            }

            $entry->order($date);
        }

        $entry
            ->makeWorkingCopy()
            ->user($request->user())
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

        if ($collection->order() === 'date') {
            $entry->order($values['date'] ?? now()->format('Y-m-d-Hi'));
        }

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
