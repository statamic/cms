<?php

namespace Statamic\Http\Controllers\CP\Collections;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\CP\Breadcrumbs;
use Statamic\Exceptions\BlueprintNotFoundException;
use Statamic\Facades\Action;
use Statamic\Facades\Asset;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\User;
use Statamic\Hooks\CP\EntriesIndexQuery;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Http\Requests\FilteredRequest;
use Statamic\Http\Resources\CP\Entries\Entries;
use Statamic\Http\Resources\CP\Entries\Entry as EntryResource;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Support\Arr;
use Statamic\Support\Str;

class EntriesController extends CpController
{
    use ExtractsFromEntryFields,
        QueriesAuthorEntries,
        QueriesFilters;

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

        $entries = (new EntriesIndexQuery($query, $collection))->paginate(request('perPage'));

        if (request('search') && $collection->hasSearchIndex()) {
            $entries->setCollection($entries->getCollection()->map->getSearchable());
        }

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
                return $collection
                    ->searchIndex()
                    ->ensureExists()
                    ->search($search)
                    ->where('collection', $collection->handle());
            }

            $query->where('title', 'like', '%'.$search.'%');
        }

        if (Site::multiEnabled()) {
            $query->whereIn('site', Site::authorized()->map->handle()->all());
        }

        if (User::current()->cant('view-other-authors-entries', [EntryContract::class, $collection])) {
            $this->queryAuthorEntries($query, $collection);
        }

        return $query;
    }

    public function edit(Request $request, $collection, $entry)
    {
        $this->authorize('view', $entry);

        $entry = $entry->fromWorkingCopy();

        $blueprint = $entry->blueprint();

        if (! $blueprint) {
            throw new BlueprintNotFoundException($entry->value('blueprint'), 'collections/'.$collection->handle());
        }

        $blueprint->setParent($entry);

        [$values, $meta, $extraValues] = $this->extractFromFields($entry, $blueprint);

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
            'extraValues' => $extraValues,
            'meta' => $meta,
            'collection' => $collection->handle(),
            'collectionHasRoutes' => ! is_null($collection->route($entry->locale())),
            'blueprint' => $blueprint->toPublishArray(),
            'readOnly' => User::current()->cant('edit', $entry),
            'locale' => $entry->locale(),
            'localizedFields' => $entry->data()->keys()->all(),
            'originBehavior' => $collection->originBehavior(),
            'isRoot' => $entry->isRoot(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'permalink' => $entry->absoluteUrl(),
            'localizations' => $this->getAuthorizedSitesForCollection($collection)->map(function ($handle) use ($entry) {
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
            })->values()->all(),
            'hasWorkingCopy' => $entry->hasWorkingCopy(),
            'preloadedAssets' => $this->extractAssetsFromValues($values),
            'revisionsEnabled' => $entry->revisionsEnabled(),
            'breadcrumbs' => $this->breadcrumbs($collection),
            'canManagePublishState' => User::current()->can('publish', $entry),
            'previewTargets' => $collection->previewTargets()->all(),
            'autosaveInterval' => $collection->autosaveInterval(),
            'itemActions' => Action::for($entry, ['collection' => $collection->handle(), 'view' => 'form']),
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

        $blueprint = $entry->blueprint();

        $data = $request->except('id');

        if (User::current()->cant('edit-other-authors-entries', [EntryContract::class, $collection, $blueprint])) {
            $data['author'] = Arr::wrap($entry->value('author'));
        }

        $fields = $entry
            ->blueprint()
            ->ensureField('published', ['type' => 'toggle'])
            ->fields()
            ->addValues($data);

        $fields
            ->validator()
            ->withRules(Entry::updateRules($collection, $entry))
            ->withReplacements([
                'id' => $entry->id(),
                'collection' => $collection->handle(),
                'site' => $entry->locale(),
            ])->validate();

        $values = $fields->process()->values();

        if ($explicitBlueprint = $values->pull('blueprint')) {
            $entry->blueprint($explicitBlueprint);
        }

        $values = $values->except(['slug', 'published']);

        if ($entry->collection()->dated()) {
            $date = $entry->blueprint()->field('date')->fieldtype()->augment($values->pull('date'));
            if ($entry->hasOrigin()) {
                $entry->date(in_array('date', $request->input('_localized')) ? $date : null);
            } else {
                $entry->date($date);
            }
        }

        if ($entry->hasOrigin()) {
            $entry->data($values->only($request->input('_localized')));
        } else {
            $entry->merge($values);
        }

        $entry->slug($this->resolveSlug($request));

        if ($structure = $collection->structure()) {
            $tree = $entry->structure()->in($entry->locale());
        }

        $parent = $values->get('parent');

        if ($structure && ! $collection->orderable()) {
            $this->validateParent($entry, $tree, $parent);

            if (! $entry->revisionsEnabled()) {
                $entry->afterSave(function ($entry) use ($parent, $tree) {
                    if ($parent && optional($tree->find($parent))->isRoot()) {
                        $parent = null;
                    }

                    $tree
                        ->move($entry->id(), $parent)
                        ->save();
                });

                $entry->remove('parent');
            }
        }

        $this->validateUniqueUri($entry, $tree ?? null, $parent ?? null);

        if ($entry->revisionsEnabled() && $entry->published()) {
            $saved = $entry
                ->makeWorkingCopy()
                ->user(User::current())
                ->save();

            // catch any changes through RevisionSaving event
            $entry = $entry->fromWorkingCopy();
        } else {
            if (! $entry->revisionsEnabled() && User::current()->can('publish', $entry)) {
                $entry->published($request->published);
            }

            $saved = $entry->updateLastModified(User::current())->save();
        }

        [$values, $meta, $extraValues] = $this->extractFromFields($entry, $blueprint);

        return [
            'data' => array_merge((new EntryResource($entry->fresh()))->resolve()['data'], [
                'values' => $values,
                'extraValues' => $extraValues,
            ]),
            'saved' => $saved,
        ];
    }

    public function create(Request $request, $collection, $site)
    {
        $this->authorize('create', [EntryContract::class, $collection, $site]);

        if ($response = $this->ensureCollectionIsAvailableOnSite($collection, $site)) {
            return $response;
        }

        $blueprint = $collection->entryBlueprint($request->blueprint);

        if (! $blueprint) {
            throw new \Exception(__('A valid blueprint is required.'));
        }

        $values = Entry::make()->collection($collection)->values()->all();

        if ($collection->hasStructure() && $request->parent) {
            $values['parent'] = $request->parent;
        }

        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        $values = collect([
            'title' => null,
            'slug' => null,
            'published' => $collection->defaultPublishState(),
        ])->merge($fields->values());

        $viewData = [
            'title' => $collection->createLabel(),
            'actions' => [
                'save' => cp_route('collections.entries.store', [$collection->handle(), $site->handle()]),
                'editBlueprint' => cp_route('collections.blueprints.edit', [$collection, $blueprint]),
            ],
            'values' => $values->all(),
            'extraValues' => [
                'depth' => 1,
            ],
            'meta' => $fields->meta(),
            'collection' => $collection->handle(),
            'collectionCreateLabel' => $collection->createLabel(),
            'collectionHasRoutes' => ! is_null($collection->route($site->handle())),
            'blueprint' => $blueprint->toPublishArray(),
            'published' => $collection->defaultPublishState(),
            'locale' => $site->handle(),
            'localizations' => $this->getAuthorizedSitesForCollection($collection)->map(function ($handle) use ($collection, $site, $blueprint) {
                return [
                    'handle' => $handle,
                    'name' => Site::get($handle)->name(),
                    'active' => $handle === $site->handle(),
                    'exists' => false,
                    'published' => false,
                    'url' => cp_route('collections.entries.create', [$collection->handle(), $handle, 'blueprint' => $blueprint->handle()]),
                    'livePreviewUrl' => $collection->route($handle) ? cp_route('collections.entries.preview.create', [$collection->handle(), $handle]) : null,
                ];
            })->values()->all(),
            'revisionsEnabled' => $collection->revisionsEnabled(),
            'breadcrumbs' => $this->breadcrumbs($collection),
            'canManagePublishState' => User::current()->can('publish '.$collection->handle().' entries'),
            'previewTargets' => $collection->previewTargets()->all(),
            'autosaveInterval' => $collection->autosaveInterval(),
        ];

        if ($request->wantsJson()) {
            return collect($viewData);
        }

        return view('statamic::entries.create', $viewData);
    }

    public function store(Request $request, $collection, $site)
    {
        $this->authorize('store', [EntryContract::class, $collection]);

        $blueprint = $collection->entryBlueprint($request->_blueprint);

        $data = $request->all();

        if (User::current()->cant('edit-other-authors-entries', [EntryContract::class, $collection, $blueprint])) {
            $data['author'] = [User::current()->id()];
        }

        $fields = $blueprint
            ->ensureField('published', ['type' => 'toggle'])
            ->fields()
            ->addValues($data);

        $fields
            ->validator()
            ->withRules(Entry::createRules($collection, $site))
            ->withReplacements([
                'collection' => $collection->handle(),
                'site' => $site->handle(),
            ])->validate();

        $values = $fields->process()->values()->except(['slug', 'blueprint', 'published']);

        $entry = Entry::make()
            ->collection($collection)
            ->blueprint($request->_blueprint)
            ->locale($site->handle())
            ->published($request->get('published'))
            ->slug($this->resolveSlug($request));

        if ($collection->dated()) {
            $entry->date($blueprint->field('date')->fieldtype()->augment($values->pull('date')));
        }

        $entry->data($values);

        if ($structure = $collection->structure()) {
            $tree = $structure->in($site->handle());
        }

        if ($structure && ! $collection->orderable()) {
            $parent = $values['parent'] ?? null;
            $entry->afterSave(function ($entry) use ($parent, $tree) {
                if ($parent && optional($tree->find($parent))->isRoot()) {
                    $parent = null;
                }

                $tree->appendTo($parent, $entry)->save();
            });
        }

        $this->validateUniqueUri($entry, $tree ?? null, $parent ?? null);

        if ($entry->revisionsEnabled()) {
            $saved = $entry->store([
                'message' => $request->message,
                'user' => User::current(),
            ]);
        } else {
            $saved = $entry->updateLastModified(User::current())->save();
        }

        return [
            'data' => (new EntryResource($entry))->resolve()['data'],
            'saved' => $saved,
        ];
    }

    private function resolveSlug($request)
    {
        return function ($entry) use ($request) {
            if ($request->slug) {
                return $request->slug;
            }

            if ($entry->blueprint()->hasField('slug')) {
                return Str::slug($request->title ?? $entry->autoGeneratedTitle(), '-', $entry->site()->lang());
            }

            return null;
        };
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

    private function validateParent($entry, $tree, $parent)
    {
        if ($entry->id() == $parent) {
            throw ValidationException::withMessages(['parent' => __('statamic::validation.parent_cannot_be_itself')]);
        }

        // If there's no parent selected, the entry will be at end of the top level, which is fine.
        // If the entry being edited is not the root, then we don't have anything to worry about.
        // If the parent is the root, that's fine, and is handled during the tree update later.
        if (! $parent || ! $entry->page()->isRoot()) {
            $maxDepth = $entry->collection()->structure()->maxDepth();

            // If a parent is selected, validate that it doesn't exceed the max depth of the structure.
            if ($parent && $maxDepth && Entry::find($parent)->page()->depth() >= $maxDepth) {
                throw ValidationException::withMessages(['parent' => __('statamic::validation.parent_exceeds_max_depth')]);
            }

            return;
        }

        // There will always be a next page since we couldn't have got this far with a single page.
        $nextTopLevelPage = $tree->pages()->all()->skip(1)->first();

        if ($nextTopLevelPage->id() === $parent || $nextTopLevelPage->pages()->all()->count() > 0) {
            throw ValidationException::withMessages(['parent' => __('statamic::validation.parent_causes_root_children')]);
        }
    }

    private function validateUniqueUri($entry, $tree, $parent)
    {
        if (! $uri = $this->entryUri($entry, $tree, $parent)) {
            return;
        }

        $existing = Entry::findByUri($uri, $entry->locale());

        if (! $existing || $existing->id() === $entry->id()) {
            return;
        }

        throw ValidationException::withMessages(['slug' => __('statamic::validation.unique_uri')]);
    }

    private function entryUri($entry, $tree, $parent)
    {
        if (! $entry->route()) {
            return null;
        }

        if (! $tree) {
            return app(\Statamic\Contracts\Routing\UrlBuilder::class)
                ->content($entry)
                ->merge([
                    'id' => $entry->id() ?? Stache::generateId(),
                ])
                ->build($entry->route());
        }

        $parent = $parent ? $tree->find($parent) : null;

        return app(\Statamic\Contracts\Routing\UrlBuilder::class)
            ->content($entry)
            ->merge([
                'parent_uri' => $parent ? $parent->uri() : null,
                'slug' => $entry->slug(),
                // 'depth' => '', // todo
                'is_root' => false,
            ])
            ->build($entry->route());
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
                'url' => $collection->breadcrumbUrl(),
            ],
        ]);
    }

    protected function getAuthorizedSitesForCollection($collection)
    {
        return $collection
            ->sites()
            ->filter(fn ($handle) => User::current()->can('view', Site::get($handle)));
    }

    protected function ensureCollectionIsAvailableOnSite($collection, $site)
    {
        if (Site::multiEnabled() && ! $collection->sites()->contains($site->handle())) {
            return redirect()->back()->with('error', __('Collection is not available on site ":handle".', ['handle' => $site->handle]));
        }
    }
}
