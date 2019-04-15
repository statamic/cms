<?php

namespace Statamic\Tags\Collection;

use Statamic\Tags\Tags;
use Illuminate\Contracts\Pagination\Paginator;

class Collection extends Tags
{
    /**
     * {{ collection:* }} ... {{ /collection:* }}
     */
    public function __call($method, $args)
    {
        $this->entries = (new Entries($this->method, $this->parameters))->get();

        return $this->output();
    }

    /**
     * {{ collection from="" }} ... {{ /collection }}
     */
    public function index()
    {
        $this->entries = (new Entries($this->get('from'), $this->parameters))->get();

        return $this->output();
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
