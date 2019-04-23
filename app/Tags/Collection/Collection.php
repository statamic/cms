<?php

namespace Statamic\Tags\Collection;

use Statamic\API;
use Statamic\Tags\Tags;
use Statamic\Data\Entries\EntryCollection;
use Illuminate\Contracts\Pagination\Paginator;

class Collection extends Tags
{
    /**
     * {{ collection:* }} ... {{ /collection:* }}
     */
    public function __call($method, $args)
    {
        $this->parameters['from'] = $this->method;

        return $this->index();
    }

    /**
     * {{ collection from="" }} ... {{ /collection }}
     */
    public function index()
    {
        $from = $this->fromCollections();

        $entries = collect(explode('|', $from))
            ->map(function ($from) {
                return (new Entries($from, $this->parameters))->get();
            })
            ->flatten(1)
            ->all();

        $this->entries = new EntryCollection($entries);

        return $this->output();
    }

    protected function fromCollections()
    {
        $from = $this->get('from') ?? $this->get('folder') ?? $this->get('use');
        $not = $this->get('not_from') ?? $this->get('not_folder') ?? $this->get('dont_use') ?? false;

        $collections = $from === '*'
            ? API\Collection::all()->map->handle()
            : collect(explode('|', $from));

        $excludedCollections = collect(explode('|', $not))->filter();

        return $collections
            ->reject(function ($collection) use ($excludedCollections) {
                return $excludedCollections->contains($collection);
            })
            ->implode('|');
    }

    protected function output()
    {
        if ($this->entries instanceof Paginator) {
            return $this->paginatedOutput();
        }

        if ($as = $this->get('as')) {
            return [$as => $this->entries];
        }

        return $this->entries;
    }

    protected function paginatedOutput()
    {
        $as = $this->get('as', 'entries');
        $paginator = $this->entries;
        $entries = $paginator->getCollection()->supplement('total_results', $paginator->total());

        return [
            $as => $entries,
            'paginate' => $this->getPaginationData($paginator),
            'total_results' => 10,
        ];
    }

    protected function getPaginationData($paginator)
    {
        return [
            'total_items'    => $paginator->total(),
            'items_per_page' => $paginator->perPage(),
            'total_pages'    => $paginator->lastPage(),
            'current_page'   => $paginator->currentPage(),
            'prev_page'      => $paginator->previousPageUrl(),
            'next_page'      => $paginator->nextPageUrl(),
            'auto_links'     => $paginator->render('pagination::default'),
            'links'          => $paginator->renderArray()
        ];
    }
}
