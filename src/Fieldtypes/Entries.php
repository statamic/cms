<?php

namespace Statamic\Fieldtypes;

use Illuminate\Support\Collection as SupportCollection;
use Statamic\Contracts\Data\Localization;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Scope;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Resources\CP\Entries\Entries as EntriesResource;
use Statamic\Http\Resources\CP\Entries\Entry as EntryResource;
use Statamic\Query\OrderedQueryBuilder;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;
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
        return array_merge(parent::configFieldItems(), [
            'create' => [
                'display' => __('Allow Creating'),
                'instructions' => __('statamic::fieldtypes.entries.config.create'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
            'collections' => [
                'display' => __('Collections'),
                'type' => 'collections',
                'mode' => 'select',
            ],
        ]);
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

        return $request->boolean('paginate', true) ? $query->paginate() : $query->get();
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
        $column = $request->get('sort');

        if (! $column && ! $request->search) {
            $column = 'title'; // todo: get from collection or config
        }

        return $column;
    }

    public function getSortDirection($request)
    {
        $order = $request->get('order', 'asc');

        if (! $request->sort && ! $request->search) {
            // $order = 'asc'; // todo: get from collection or config
        }

        return $order;
    }

    protected function getIndexQuery($request)
    {
        $query = Entry::query();

        if ($search = $request->search) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        if ($site = $request->site) {
            $query->where('site', $site);
        }

        if ($request->exclusions) {
            $query->whereNotIn('id', $request->exclusions);
        }

        return $query;
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

        $query = (new OrderedQueryBuilder(Entry::query(), $ids))
            ->whereIn('id', $ids)
            ->where('status', 'published');

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
        return $this->getBlueprint()->columns()->values()->all();
    }

    protected function getItemsForPreProcessIndex($values): SupportCollection
    {
        if (! $augmented = $this->augment($values)) {
            return collect();
        }

        return $this->config('max_items') === 1 ? collect([$augmented]) : $augmented->get();
    }
}
