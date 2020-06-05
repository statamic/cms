<?php

namespace Statamic\Fieldtypes;

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

        return $query->paginate()->preProcessForIndex();
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

        return collect($collections)->map(function ($collection) {
            $collection = Collection::findByHandle($collection);

            return [
                'title' => $collection->title(),
                'url' => $collection->createEntryUrl(Site::selected()->handle()),
            ];
        })->all();
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
        if (is_string($value)) {
            $value = Entry::find($value);
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
        $collections = $this->getConfiguredCollections();

        $blueprints = collect($collections)->flatMap(function ($collection) {
            return Collection::findByHandle($collection)->entryBlueprints()->map->handle();
        })->all();

        return compact('collections', 'blueprints');
    }

    protected function getConfiguredCollections()
    {
        return empty($collections = $this->config('collections'))
            ? Collection::handles()->all()
            : $collections;
    }
}
