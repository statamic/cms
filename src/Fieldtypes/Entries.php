<?php

namespace Statamic\Fieldtypes;

use Illuminate\Support\Collection as SupportCollection;
use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\CP\Column;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Scope;
use Statamic\Facades\Search;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Resources\CP\Entries\Entries as EntriesResource;
use Statamic\Http\Resources\CP\Entries\Entry as EntryResource;
use Statamic\Query\OrderedQueryBuilder;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
use Statamic\Query\Scopes\Filters\Fields\Entries as EntriesFilter;
use Statamic\Query\StatusQueryBuilder;
use Statamic\Search\Index;
use Statamic\Search\Result;
use Statamic\Support\Arr;

class Entries extends Relationship
{
    use QueriesFilters;

    protected $categories = ['relationship'];
    protected $canEdit = true;
    protected $canCreate = true;
    protected $canSearch = true;
    protected $statusIcons = true;
    protected $formComponent = 'entry-publish-form';
    protected $activeFilterBadges;

    protected $formComponentProps = [
        'initialActions' => 'actions',
        'initialTitle' => 'title',
        'initialReference' => 'reference',
        'initialFieldset' => 'blueprint',
        'initialValues' => 'values',
        'initialLocalizedFields' => 'localizedFields',
        'initialMeta' => 'meta',
        'initialPermalink' => 'permalink',
        'initialLocalizations' => 'localizations',
        'initialHasOrigin' => 'hasOrigin',
        'initialOriginValues' => 'originValues',
        'initialOriginMeta' => 'originMeta',
        'initialSite' => 'locale',
        'initialIsWorkingCopy' => 'hasWorkingCopy',
        'initialIsRoot' => 'isRoot',
        'initialReadOnly' => 'readOnly',
        'revisionsEnabled' => 'revisionsEnabled',
        'breadcrumbs' => 'breadcrumbs',
        'collectionHandle' => 'collection',
        'canManagePublishState' => 'canManagePublishState',
        'collectionHasRoutes' => 'collectionHasRoutes',
    ];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'max_items' => [
                        'display' => __('Max Items'),
                        'instructions' => __('statamic::messages.max_items_instructions'),
                        'min' => 1,
                        'type' => 'integer',
                    ],
                    'mode' => [
                        'display' => __('UI Mode'),
                        'instructions' => __('statamic::fieldtypes.relationship.config.mode'),
                        'type' => 'radio',
                        'default' => 'default',
                        'options' => [
                            'default' => __('Stack Selector'),
                            'select' => __('Select Dropdown'),
                            'typeahead' => __('Typeahead Field'),
                        ],
                    ],
                    'create' => [
                        'display' => __('Allow Creating'),
                        'instructions' => __('statamic::fieldtypes.entries.config.create'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'collections' => [
                        'display' => __('Collections'),
                        'instructions' => __('statamic::fieldtypes.entries.config.collections'),
                        'type' => 'collections',
                        'mode' => 'select',
                    ],
                    'search_index' => [
                        'display' => __('Search Index'),
                        'instructions' => __('statamic::fieldtypes.entries.config.search_index'),
                        'type' => 'text',
                    ],
                    'query_scopes' => [
                        'display' => __('Query Scopes'),
                        'instructions' => __('statamic::fieldtypes.entries.config.query_scopes'),
                        'type' => 'taggable',
                    ],
                ],
            ],
        ];
    }

    public function getIndexItems($request)
    {
        $query = $this->getIndexQuery($request);

        $filters = $request->filters;

        if (! isset($filters['collection'])) {
            $query->whereIn('collection', $this->getConfiguredCollections());
        }

        if ($blueprints = $this->config('blueprints')) {
            $query->whereIn('blueprint', $blueprints);
        }

        $this->activeFilterBadges = $this->queryFilters($query, $filters, $this->getSelectionFilterContext());

        if ($sort = $this->getSortColumn($request)) {
            $query->orderBy($sort, $this->getSortDirection($request));
        }

        $results = ($paginate = $request->boolean('paginate', true)) ? $query->paginate() : $query->get();

        $items = $results->map(fn ($item) => $item instanceof Result ? $item->getSearchable() : $item);

        return $paginate ? $results->setCollection($items) : $items;
    }

    public function getResourceCollection($request, $items)
    {
        return (new EntriesResource($items))
            ->blueprint($this->getBlueprint($request))
            ->columnPreferenceKey("collections.{$this->getFirstCollectionFromRequest($request)->handle()}.columns")
            ->additional(['meta' => [
                'activeFilterBadges' => $this->activeFilterBadges,
            ]]);
    }

    protected function getBlueprint($request = null)
    {
        return $this->getFirstCollectionFromRequest($request)->entryBlueprint();
    }

    protected function getFirstCollectionFromRequest($request)
    {
        $collections = $request
            ? $request->input('filters.collection.collections', [])
            : [];

        if (empty($collections)) {
            $collections = $this->getConfiguredCollections();
        }

        return Collection::findByHandle(Arr::first($collections));
    }

    public function getSortColumn($request)
    {
        $column = $request->sort ?? 'title';

        if (! $request->sort && ! $request->search && count($this->getConfiguredCollections()) < 2) {
            $column = $this->getFirstCollectionFromRequest($request)->sortField();
        }

        return $column;
    }

    public function getSortDirection($request)
    {
        $order = $request->order ?? 'asc';

        if (! $request->sort && ! $request->search && count($this->getConfiguredCollections()) < 2) {
            $order = $this->getFirstCollectionFromRequest($request)->sortDirection();
        }

        return $order;
    }

    public function initialSortColumn()
    {
        return $this->getSortColumn(optional());
    }

    public function initialSortDirection()
    {
        return $this->getSortDirection(optional());
    }

    protected function getIndexQuery($request)
    {
        $query = Entry::query();

        $query = $this->toSearchQuery($query, $request);

        if ($site = $request->site) {
            $query->where('site', $site);
        }

        if ($request->exclusions) {
            $query->whereNotIn('id', $request->exclusions);
        }

        $this->applyIndexQueryScopes($query, $request->all());

        return $query;
    }

    private function toSearchQuery($query, $request)
    {
        if (! $search = $request->search) {
            return $query;
        }

        if ($index = $this->getSearchIndex($request)) {
            return $index->search($search);
        }

        return $query->where('title', 'like', '%'.$search.'%');
    }

    private function getSearchIndex($request): ?Index
    {
        $index = $this->getExplicitSearchIndex() ?? $this->getCollectionSearchIndex($request);

        return $index?->ensureExists();
    }

    private function getExplicitSearchIndex(): ?Index
    {
        return ($explicit = $this->config('search_index'))
            ? Search::in($explicit)
            : null;
    }

    private function getCollectionSearchIndex($request): ?Index
    {
        // Use the collections being filtered, or the configured collections.
        $collections = collect(
            $request->input('filters.collection.collections') ?? $this->getConfiguredCollections()
        );

        $indexes = $collections->map(fn ($handle) => Collection::findByHandle($handle)->searchIndex());

        // If all the collections use the same index, return it.
        // Even if they're all null, that's fine. It'll just return null.
        return $indexes->unique()->count() === 1
            ? $indexes->first()
            : null;
    }

    protected function getCreatables()
    {
        if ($url = $this->getCreateItemUrl()) {
            return [['url' => $url]];
        }

        $collections = $this->getConfiguredCollections();

        $user = User::current();

        return collect($collections)->flatMap(function ($collectionHandle) use ($collections, $user) {
            $collection = Collection::findByHandle($collectionHandle);

            throw_if(! $collection, new CollectionNotFoundException($collectionHandle));

            if (! $user->can('create', [EntryContract::class, $collection])) {
                return null;
            }

            $blueprints = $collection->entryBlueprints();

            return $blueprints
                ->reject->hidden()
                ->map(function ($blueprint) use ($collection, $collections, $blueprints) {
                    return [
                        'title' => $this->getCreatableTitle($collection, $blueprint, count($collections), $blueprints->count()),
                        'url' => $collection->createEntryUrl(Site::selected()->handle()).'?blueprint='.$blueprint->handle(),
                    ];
                });
        })->all();
    }

    private function getCreatableTitle($collection, $blueprint, $collectionCount, $blueprintCount)
    {
        if ($collectionCount > 1 && $blueprintCount === 1) {
            return $collection->title();
        }

        if ($collectionCount > 1 && $blueprintCount > 1) {
            return $collection->title().': '.$blueprint->title();
        }

        return $blueprint->title();
    }

    protected function toItemArray($id)
    {
        if (! $entry = Entry::find($id)) {
            return $this->invalidItemArray($id);
        }

        return (new EntryResource($entry))->resolve()['data'];
    }

    protected function collect($value)
    {
        return new \Statamic\Entries\EntryCollection($value);
    }

    public function augment($values)
    {
        $site = Site::current()->handle();
        if (($parent = $this->field()->parent()) && $parent instanceof Localization) {
            $site = $parent->locale();
        }

        $ids = (new OrderedQueryBuilder(Entry::query(), $ids = Arr::wrap($values)))
            ->whereIn('id', $ids)
            ->get()
            ->map(function ($entry) use ($site) {
                return optional($entry->in($site))->id();
            })
            ->filter()
            ->all();

        $query = (new StatusQueryBuilder(new OrderedQueryBuilder(Entry::query(), $ids)))
            ->whereIn('id', $ids);

        return $this->config('max_items') === 1 ? $query->first() : $query;
    }

    public function shallowAugment($values)
    {
        $items = $this->augment($values);

        if ($this->config('max_items') === 1) {
            $items = collect([$items]);
        } else {
            $items = $items->get();
        }

        $items = $items->filter()->map(function ($item) {
            return $item->toShallowAugmentedCollection();
        })->collect();

        return $this->config('max_items') === 1 ? $items->first() : $items;
    }

    public function getSelectionFilters()
    {
        return Scope::filters('entries-fieldtype', $this->getSelectionFilterContext());
    }

    protected function getSelectionFilterContext()
    {
        return ['collections' => $this->getConfiguredCollections()];
    }

    protected function getConfiguredCollections()
    {
        return empty($collections = $this->config('collections'))
            ? Collection::handles()->all()
            : $collections;
    }

    public function toGqlType()
    {
        $type = GraphQL::type('EntryInterface');

        if ($this->config('max_items') !== 1) {
            $type = GraphQL::listOf($type);
        }

        return $type;
    }

    public function getColumns()
    {
        if (count($this->getConfiguredCollections()) === 1) {
            $columns = $this->getBlueprint()->columns();

            $status = Column::make('status')
                ->listable(true)
                ->visible(true)
                ->defaultVisibility(true)
                ->sortable(false);

            $columns->put('status', $status);

            $columns->setPreferred("collections.{$this->getConfiguredCollections()[0]}.columns");

            return $columns->rejectUnlisted()->values();
        }

        return $this->getBlueprint()->columns()->values()->all();
    }

    protected function getItemsForPreProcessIndex($values): SupportCollection
    {
        if (! $augmented = $this->augment($values)) {
            return collect();
        }

        return $this->config('max_items') === 1
            ? collect([$augmented])
            : $augmented->whereAnyStatus()->get();
    }

    public function filter()
    {
        return new EntriesFilter($this);
    }

    public function preload()
    {
        $collection = count($this->getConfiguredCollections()) === 1
            ? Collection::findByHandle($this->getConfiguredCollections()[0])
            : null;

        if (! $collection || ! $collection->hasStructure()) {
            return parent::preload();
        }

        return array_merge(parent::preload(), ['tree' => [
            'title' => $collection->title(),
            'url' => cp_route('collections.tree.index', $collection),
            'showSlugs' => $collection->structure()->showSlugs(),
            'expectsRoot' => $collection->structure()->expectsRoot(),
        ]]);
    }
}
