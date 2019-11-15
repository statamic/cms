<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Scope;
use Statamic\Facades\Site;
use Statamic\Http\Resources\CP\Entries\Entries as EntriesResource;
use Statamic\Http\Resources\CP\Entries\Entry as EntryResource;

class Entries extends Relationship
{
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
    ];

    protected $extraConfigFields = [
        'collections' => [
            'type' => 'collections'
        ],
    ];

    public function getIndexItems($request)
    {
        $this->updateRequest($request);

        $query = $this->getIndexQuery($request);

        foreach ($request->filters as $handle => $values) {
            Scope::find($handle, $this->getSelectionFilterContext($request))->apply($query, $values);
        }

        if ($sort = $this->getSortColumn($request)) {
            $query->orderBy($sort, $this->getSortDirection($request));
        }

        return $query->paginate()->preProcessForIndex();
    }

    protected function updateRequest($request)
    {
        if (! $request->filters->has('collection')) {
            $request->filters['collection'] = ['value' => []];
        }
    }

    public function getResourceCollection($request, $items)
    {
        return (new EntriesResource($items))
            ->blueprint($this->getBlueprint($request))
            ->columnPreferenceKey("collections.{$this->getFirstCollectionFromRequest($request)->handle()}.columns")
            ->additional(['meta' => [
                'sortColumn' => $this->getSortColumn($request),
                'filters' => $this->getSelectionFilters($request),
                'activeFilters' => $this->getActiveFilters($request),
            ]]);
    }

    protected function getBlueprint($request)
    {
        return $this->getFirstCollectionFromRequest($request)->entryBlueprint();
    }

    protected function getFirstCollectionFromRequest($request)
    {
        $collections = $request->filters['collection']['value'];

        if (empty($collections)) {
            $collections = $this->getConfiguredCollections();
        }

        return Collection::findByHandle($collections[0]);
    }

    public function getSortColumn($request)
    {
        $column = $request->get('sort');

        if (!$column && !$request->search) {
            $column = 'title'; // todo: get from collection or config
        }

        return $column;
    }

    public function getSortDirection($request)
    {
        $order = $request->get('order', 'asc');

        if (!$request->sort && !$request->search) {
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
        if ($entry = Entry::find($value)) {
            return $entry;
        }
    }

    protected function getSelectionFilters($request)
    {
        return Scope::filters('entries-fieldtype', $this->getSelectionFilterContext($request));
    }

    protected function getSelectionFilterContext($request)
    {
        return [
            'collections' => $this->getConfiguredCollections(),
            'blueprints' => [$this->getBlueprint($request)->handle()]
        ];
    }

    protected function getActiveFilters($request)
    {
        $filters = $request->filters;

        if (empty($filters['collection']['value'])) {
            unset($filters['collection']);
        }

        return $filters;
    }

    protected function getConfiguredCollections()
    {
        return empty($collections = $this->config('collections'))
            ? Collection::handles()->all()
            : $collections;
    }
}
