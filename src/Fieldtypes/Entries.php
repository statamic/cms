<?php

namespace Statamic\Fieldtypes;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Statamic\Contracts\Data\Localization;
use Statamic\Exceptions\CollectionNotFoundException;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Scope;
use Statamic\Facades\Site;
use Statamic\Http\Resources\CP\Entries\Entries as EntriesResource;
use Statamic\Http\Resources\CP\Entries\Entry as EntryResource;
use Statamic\Query\Scopes\Filters\Concerns\QueriesFilters;

class Entries extends Relationship
{
    use QueriesFilters;

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
    ];

    protected function configFieldItems(): array
    {
        return array_merge(parent::configFieldItems(), [
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

        $this->activeFilterBadges = $this->queryFilters($query, $filters, $this->getSelectionFilterContext());

        if ($sort = $this->getSortColumn($request)) {
            $query->orderBy($sort, $this->getSortDirection($request));
        }

        $items = $request->boolean('paginate', true) ? $query->paginate() : $query->get();

        return $items->preProcessForIndex();
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

    protected function getBlueprint($request)
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

        return Collection::findByHandle($collections[0]);
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

        return collect($collections)->flatMap(function ($collectionHandle) use ($collections) {
            $collection = Collection::findByHandle($collectionHandle);

            throw_if(! $collection, new CollectionNotFoundException($collectionHandle));

            $blueprints = $collection->entryBlueprints();

            return $blueprints->map(function ($blueprint) use ($collection, $collections, $blueprints) {
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

        return (new EntryResource($entry))->resolve();
    }

    protected function augmentValue($value)
    {
        if (! is_object($value)) {
            $value = Entry::find($value);
        }
        if ($value != null && $parent = $this->field()->parent()) {
            $site = $parent instanceof Localization ? $parent->locale() : Site::current()->handle();
            $value = $value->in($site);
        }

        return $value;
    }

    protected function shallowAugmentValue($value)
    {
        return $value->toShallowAugmentedCollection();
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

    public function graphQlType(): Type
    {
        $type = GraphQL::type('EntryInterface');

        if ($this->config('max_items') !== 1) {
            $type = Type::listOf($type);
        }

        return $type;
    }
}
