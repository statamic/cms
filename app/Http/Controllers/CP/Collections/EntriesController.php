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
    public function index(FilteredSiteRequest $request, $collection)
    {
        $this->authorize('view', $collection);

        $query = $this->indexQuery($collection);

        $this->filter($query, $request->filters);

        $sortField = request('sort');
        $sortDirection = request('order');

        if (!$sortField && !request('search')) {
            $sortField = $collection->sortField();
            $sortDirection = $collection->sortDirection();
        }

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $paginator = $query->paginate(request('perPage'));

        $entries = $paginator->supplement(function ($entry) {
            return [
                'viewable' => me()->can('view', $entry),
                'editable' => me()->can('edit', $entry),
                'deleteable' => me()->can('delete', $entry)
            ];
        })->preProcessForIndex();

        if ($collection->dated()) {
            $entries->supplement('date', function ($entry) {
                return $entry->date()->inPreferredFormat();
            });
        }

        $columns = $collection->entryBlueprint()
            ->columns()
            ->setPreferred("collections.{$collection->handle()}.columns")
            ->rejectUnlisted()
            ->values();

        return Resource::collection($paginator)->additional(['meta' => [
            'filters' => $request->filters,
            'sortColumn' => $sortField,
            'columns' => $columns,
        ]]);
    }

    protected function filter($query, $filters)
    {
        foreach ($filters as $handle => $values) {
            $class = app('statamic.scopes')->get($handle);
            $filter = app($class);
            $filter->apply($query, $values);
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

    public function edit(Request $request, $collection, $entry)
    {
        $this->authorize('view', $entry);

        $entry = $entry->fromWorkingCopy();

        $blueprint = $entry->blueprint();

        event(new PublishBlueprintFound($blueprint, 'entry', $entry));

        [$values, $meta] = $this->extractFromFields($entry, $blueprint);

        if ($hasOrigin = $entry->hasOrigin()) {
            [$originValues, $originMeta] = $this->extractFromFields($entry->origin(), $blueprint);
        }

        $viewData = [
            'reference' => $entry->reference(),
            'editing' => true,
            'actions' => [
                'save' => $entry->updateUrl(),
                'publish' => $entry->publishUrl(),
                'revisions' => $entry->revisionsUrl(),
                'restore' => $entry->restoreRevisionUrl(),
                'createRevision' => $entry->createRevisionUrl(),
            ],
            'values' => array_merge($values, ['id' => $entry->id()]),
            'meta' => $meta,
            'collection' => $this->collectionToArray($collection),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => $request->user()->cant('edit', $entry),
            'locale' => $entry->locale(),
            'localizedFields' => array_keys($entry->data()),
            'isRoot' => $entry->isRoot(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? [],
            'originMeta' => $originMeta ?? [],
            'permalink' => $entry->absoluteUrl(),
            'localizations' => $collection->sites()->map(function ($handle) use ($entry) {
                $localized = $entry->in($handle);
                $exists = $localized !== null;
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $entry->locale(),
                    'exists' => $exists,
                    'root' => $exists ? $localized->isRoot() : false,
                    'origin' => $exists ? $localized->id() === optional($entry->origin())->id() : null,
                    'published' => $exists ? $localized->published() : false,
                    'url' => $exists ? $localized->editUrl() : null,
                ];
            })->all()
        ];

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($request->has('created')) {
            session()->now('success', __('Entry created'));
        }

        return view('statamic::entries.edit', array_merge($viewData, [
            'entry' => $entry
        ]));
    }

    public function update(Request $request, $collection, $entry)
    {
        $this->authorize('update', $entry);

        $entry = $entry->fromWorkingCopy();

        $fields = $entry->blueprint()->fields()->addValues($request->except('id'))->process();

        $validation = (new Validation)->fields($fields)->withRules([
            'title' => 'required',
            'slug' => 'required|alpha_dash',
        ]);

        $request->validate($validation->rules());

        $values = array_except($fields->values(), ['slug', 'date']);

        if ($entry->hasOrigin()) {
            $values = array_only($values, $request->input('_localized'));
        }

        $entry
            ->data($values)
            ->slug($request->slug);

        if ($entry->collection()->dated()) {
            $entry->date($this->formatDateForSaving($request->date));
        }

        if ($entry->revisionsEnabled() && $entry->published()) {
            $entry
                ->makeWorkingCopy()
                ->user($request->user())
                ->save();
        } else {
            if (! $entry->revisionsEnabled()) {
                $entry->published($request->published);
            }

            $entry
                ->set('updated_by', $request->user()->id())
                ->set('updated_at', now()->timestamp)
                ->save();
        }

        return $entry->toArray();
    }

    public function create(Request $request, $collection, $site)
    {
        $this->authorize('create', [EntryContract::class, $collection]);

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
                'save' => cp_route('collections.entries.store', [$collection->handle(), $site->handle()])
            ],
            'values' => $values,
            'meta' => $fields->meta(),
            'collection' => $this->collectionToArray($collection),
            'blueprint' => $blueprint->toPublishArray(),
            'localizations' => $collection->sites()->map(function ($handle) use ($collection, $site) {
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $site->handle(),
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
        $this->authorize('create', [EntryContract::class, $collection]);

        $fields = Blueprint::find($request->blueprint)->fields()->addValues($request->all())->process();

        $validation = (new Validation)->fields($fields)->withRules([
            'title' => 'required',
            'slug' => 'required',
        ]);

        $request->validate($validation->rules());

        $values = array_except($fields->values(), ['slug']);

        $entry = Entry::make()
            ->collection($collection)
            ->locale($site->handle())
            ->published(false)
            ->slug($request->slug)
            ->data($values);

        if ($collection->dated()) {
            $date = $values['date']
                ? $this->formatDateForSaving($values['date'])
                : now()->format('Y-m-d-Hi');
            $entry->date($date);
        }

        if ($entry->revisionsEnabled()) {
            $entry->store([
                'message' => $request->message,
                'user' => $request->user(),
            ]);
        } else {
            $entry
                ->set('updated_by', $request->user()->id())
                ->set('updated_at', now()->timestamp)
                ->save();
        }

        if ($structure = $collection->structure()) {
            $tree = $structure
                ->in($site->handle())
                ->append($entry)
                ->save();
        }

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

    protected function extractFromFields($entry, $blueprint)
    {
        $fields = $blueprint
            ->fields()
            ->addValues($entry->values())
            ->preProcess();

        $values = array_merge($fields->values(), [
            'title' => $entry->value('title'),
            'slug' => $entry->slug()
        ]);

        if ($entry->collection()->dated()) {
            $datetime = substr($entry->date()->toDateTimeString(), 0, 16);
            $datetime = ($entry->hasTime()) ? $datetime : substr($datetime, 0, 10);
            $values['date'] = $datetime;
        }

        return [$values, $fields->meta()];
    }

    protected function formatDateForSaving($date)
    {
        // If there's a time, adjust the format into a datetime order string.
        if (strlen($date) > 10) {
            $date = str_replace(':', '', $date);
            $date = str_replace(' ', '-', $date);
        }

        return $date;
    }
}
