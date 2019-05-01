<?php

namespace Statamic\Tags\Collection;

use Statamic\API\URL;
use Statamic\API\Entry;
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
        $this->entries = $this->entries()->get();

        return $this->output();
    }

    /**
     * {{ collection:count from="" }} ... {{ /collection:count }}
     */
    public function count()
    {
        return $this->entries()->count();
    }

    /**
     * {{ collection:next from="" }} ... {{ /collection:next }}
     */
    public function next()
    {
        $this->entries = $this->entries()->next($this->currentEntry());

        return $this->output();
    }

    /**
     * {{ collection:previous from="" }} ... {{ /collection:previous }}
     */
    public function previous()
    {
        $this->entries = $this->entries()->previous($this->currentEntry());

        return $this->output();
    }

    protected function entries()
    {
        return new Entries($this->parameters);
    }

    protected function currentEntry()
    {
        return Entry::find($this->get('current', $this->getContext('id')));
    }

    protected function output()
    {
        if ($this->entries instanceof Paginator) {
            return $this->paginatedOutput();
        }

        if ($as = $this->get('as')) {
            return array_merge([$as => $this->entries], $this->extraOutput());
        }

        return $this->entries;
    }

    protected function extraOutput()
    {
        $extra = [];

        $extra['total_results'] = $this->entries->count();

        if ($this->entries->isEmpty()) {
            $extra['no_results'] = true;
        }

        return $extra;
    }

    protected function paginatedOutput()
    {
        $as = $this->get('as', 'entries');
        $paginator = $this->entries;
        $entries = $paginator->getCollection()->supplement('total_results', $paginator->total());

        return array_merge([
            $as => $entries,
            'paginate' => $this->getPaginationData($paginator)
        ], $this->extraOutput());
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
