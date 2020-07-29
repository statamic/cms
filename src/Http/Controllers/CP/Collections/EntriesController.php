<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Asset;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Http\Resources\CP\Entries\Entries;
use Statamic\Http\Resources\CP\Entries\Entry as EntryResource;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class EntriesController extends CpController
{
    use QueriesFilters;

    public function index(FilteredRequest $request, $collection)
    {
        $this->authorize('view', $collection);

        $query = $this->indexQuery($collection);

        $activeFilterBadges = $this->queryFilters($query, $request->filters, [
            'collection' => $collection->handle(),
            'blueprints' => $collection->entryBlueprints()->map->handle(),
        ]);

        $sortField = request('sort');
        $sortDirection = request('order', 'asc');

        if (! $sortField && ! request('search')) {
            $sortField = $collection->sortField();
            $sortDirection = $collection->sortDirection();
        }

        if ($sortField) {
            $query->orderBy($sortField, $sortDirection);
        }

        $entries = $query->paginate(request('perPage'));

        return (new Entries($entries))
            ->blueprint($collection->entryBlueprint())
            ->columnPreferenceKey("collections.{$collection->handle()}.columns")
            ->additional(['meta' => [
                'activeFilterBadges' => $activeFilterBadges,
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

    public function edit(Request $request, $collection, $entry)
    {
        $this->authorize('view', $entry);

        $entry = $entry->fromWorkingCopy();

        $blueprint = $entry->blueprint();

        [$values, $meta] = $this->extractFromFields($entry, $blueprint);

        if ($hasOrigin = $entry->hasOrigin()) {
            [$originValues, $originMeta] = $this->extractFromFields($entry->origin(), $blueprint);
        }

        $viewData = [
            'title' => $entry->value('title'),
            'reference' => $entry->reference(),
            'editing' => true,
            'actions' => [
                'save' => $entry->updateUrl(),
                'publish' => $entry->publishUrl(),
                'unpublish' => $entry->unpublishUrl(),
                'revisions' => $entry->revisionsUrl(),
                'restore' => $entry->restoreRevisionUrl(),
                'createRevision' => $entry->createRevisionUrl(),
                'editBlueprint' => cp_route('collections.blueprints.edit', [$collection, $blueprint]),
            ],
            'values' => array_merge($values, ['id' => $entry->id()]),
            'meta' => $meta,
            'collection' => $collection->handle(),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => User::fromUser($request->user())->cant('edit', $entry),
            'locale' => $entry->locale(),
            'localizedFields' => $entry->data()->keys()->all(),
            'isRoot' => $entry->isRoot(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
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
                    'status' => $exists ? $localized->status() : null,
                    'url' => $exists ? $localized->editUrl() : null,
                    'livePreviewUrl' => $exists ? $localized->livePreviewUrl() : null,
                ];
            })->all(),
            'hasWorkingCopy' => $entry->hasWorkingCopy(),
            'preloadedAssets' => $this->extractAssetsFromValues($values),
            'revisionsEnabled' => $entry->revisionsEnabled(),
            'breadcrumbs' => $this->breadcrumbs($collection),
        ];

        if ($request->wantsJson()) {
            return collect($viewData);
        }

        if ($request->has('created')) {
            session()->now('success', __('Entry created'));
        }

        return view('statamic::entries.edit', array_merge($viewData, [
            'entry' => $entry,
        ]));
    }

    public function update(Request $request, $collection, $entry)
    {
        $this->authorize('update', $entry);

        $entry = $entry->fromWorkingCopy();

        $fields = $entry->blueprint()->fields()->addValues($request->except('id'));

        $fields->validate(Entry::updateRules($collection, $entry));

        $values = $fields->process()->values();

        $parent = $values->pull('parent');

        $values = $values->except(['slug', 'date']);

        if ($entry->hasOrigin()) {
            $entry->data($values->only($request->input('_localized')));
        } else {
            $entry->merge($values);
        }

        $entry->slug($request->slug);

        if ($entry->collection()->dated()) {
            $entry->date($this->formatDateForSaving($request->date));
        }

        if ($collection->structure() && ! $collection->orderable()) {
            $entry->afterSave(function ($entry) use ($parent) {
                $entry->structure()
                    ->in($entry->locale())
                    ->move($entry->id(), $parent)
                    ->save();
            });
        }

        if ($entry->revisionsEnabled() && $entry->published()) {
            $entry
                ->makeWorkingCopy()
                ->user(User::fromUser($request->user()))
                ->save();
        } else {
            if (! $entry->revisionsEnabled()) {
                $entry->published($request->published);
            }

            $entry
                ->set('updated_by', User::fromUser($request->user())->id())
                ->set('updated_at', now()->timestamp)
                ->save();
        }

        return new EntryResource($entry->fresh());
    }

    public function create(Request $request, $collection, $site)
    {
        $this->authorize('create', [EntryContract::class, $collection]);

        $blueprint = $collection->entryBlueprint($request->blueprint);

        if (! $blueprint) {
            throw new \Exception(__('A valid blueprint is required.'));
        }

        $values = [];

        if ($collection->hasStructure() && $request->parent) {
            $values['parent'] = $request->parent;
        }

        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = $fields->values()->merge([
            'title' => null,
            'slug' => null,
            'published' => $collection->defaultPublishState(),
        ]);

        if ($collection->dated()) {
            $values['date'] = substr(now()->toDateTimeString(), 0, 10);
        }

        $viewData = [
            'title' => __('Create Entry'),
            'actions' => [
                'save' => cp_route('collections.entries.store', [$collection->handle(), $site->handle()]),
            ],
            'values' => $values->all(),
            'meta' => $fields->meta(),
            'collection' => $collection->handle(),
            'blueprint' => $blueprint->toPublishArray(),
            'published' => $collection->defaultPublishState(),
            'localizations' => $collection->sites()->map(function ($handle) use ($collection, $site) {
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $site->handle(),
                    'exists' => false,
                    'published' => false,
                    'url' => cp_route('collections.entries.create', [$collection->handle(), $handle]),
                    'livePreviewUrl' => $collection->route($handle) ? cp_route('collections.entries.preview.create', [$collection->handle(), $handle]) : null,
                ];
            })->all(),
            'revisionsEnabled' => $collection->revisionsEnabled(),
            'breadcrumbs' => $this->breadcrumbs($collection),
        ];

        if ($request->wantsJson()) {
            return collect($viewData);
        }

        return view('statamic::entries.create', $viewData);
    }

    public function store(Request $request, $collection, $site)
    {
        $this->authorize('store', [EntryContract::class, $collection]);

        $blueprint = $collection->entryBlueprint($request->blueprint);

        $fields = $blueprint->fields()->addValues($request->all());

        $fields->validate(Entry::createRules($collection, $site));

        $values = $fields->process()->values()->except(['slug', 'date', 'blueprint']);

        $entry = Entry::make()
            ->collection($collection)
            ->blueprint($request->blueprint)
            ->locale($site->handle())
            ->published($request->get('published'))
            ->slug($request->slug)
            ->data($values);

        if ($collection->dated()) {
            $entry->date($this->formatDateForSaving($request->date));
        }

        if (($structure = $collection->structure()) && ! $collection->orderable()) {
            $tree = $structure->in($site->handle());
            $parent = $values['parent'] ?? null;
            $entry->afterSave(function ($entry) use ($parent, $tree) {
                $tree->appendTo($parent, $entry)->save();
            });
        }

        if ($entry->revisionsEnabled()) {
            $entry->store([
                'message' => $request->message,
                'user' => User::fromUser($request->user()),
            ]);
        } else {
            $entry
                ->set('updated_by', User::fromUser($request->user())->id())
                ->set('updated_at', now()->timestamp)
                ->save();
        }

        return new EntryResource($entry);
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

    protected function extractFromFields($entry, $blueprint)
    {
        $values = $entry->values()->all();

        if ($entry->hasStructure()) {
            $values['parent'] = array_filter([optional($entry->parent())->id()]);
        }

        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = $fields->values()->merge([
            'title' => $entry->value('title'),
            'slug' => $entry->slug(),
            'published' => $entry->published(),
        ]);

        if ($entry->collection()->dated()) {
            $datetime = substr($entry->date()->toDateTimeString(), 0, 16);
            $datetime = ($entry->hasTime()) ? $datetime : substr($datetime, 0, 10);
            $values['date'] = $datetime;
        }

        return [$values->all(), $fields->meta()];
    }

    protected function extractAssetsFromValues($values)
    {
        return collect($values)
            ->filter(function ($value) {
                return is_string($value);
            })
            ->map(function ($value) {
                preg_match_all('/"asset::([^"]+)"/', $value, $matches);

                return str_replace('\/', '/', $matches[1]) ?? null;
            })
            ->flatten(2)
            ->unique()
            ->map(function ($id) {
                return Asset::find($id);
            })
            ->filter()
            ->values();
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

    protected function breadcrumbs($collection)
    {
        return new Breadcrumbs([
            [
                'text' => __('Collections'),
                'url' => cp_route('collections.index'),
            ],
            [
                'text' => $collection->title(),
                'url' => $collection->showUrl(),
            ],
        ]);
    }
}
