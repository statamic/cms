<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;

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
        $query = $this->getIndexQuery($request);

        if ($sort = $this->getSortColumn($request)) {
            $query->orderBy($sort, $this->getSortDirection($request));
        }

        return $query->paginate()->preProcessForIndex();
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

        if ($collections = $request->collections) {
            $query->whereIn('collection', $collections);
        } elseif ($collections = $this->config('collections')) {
            $query->whereIn('collection', $collections);
        }

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

        $collections = $this->config('collections', Collection::handles());

        return collect($collections)->map(function ($collection) {
            $collection = Collection::findByHandle($collection);

            return [
                'title' => $collection->title(),
                'url' => $collection->createEntryUrl(Site::selected()->handle()),
            ];
        })->all();
    }

    protected function getColumns()
    {
        return [
            Column::make('title'),
            Column::make('collection'),
            Column::make('url')->label('URL'),
        ];
    }

    protected function toItemArray($id)
    {
        if ($entry = Entry::find($id)) {
            return $entry->toArray();
        }

        return $this->invalidItemArray($id);
    }

    protected function augmentValue($value)
    {
        if ($entry = Entry::find($value)) {
            return $entry;
        }
    }
}
