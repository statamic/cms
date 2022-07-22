<?php

namespace Statamic\Tags\Concerns;

use Illuminate\Contracts\Pagination\Paginator;

trait OutputsItems
{
    protected function output($items)
    {
        if ($items instanceof Paginator) {
            return $this->paginatedOutput($items);
        }

        if ($as = $this->params->get('as')) {
            return array_merge([$as => $items], $this->extraOutput($items));
        }

        return $items;
    }

    protected function extraOutput($items)
    {
        $extra = [];

        $extra['total_results'] = $items->count();
        $extra['no_results'] = $items->isEmpty();

        return $extra;
    }

    protected function paginatedOutput($paginator)
    {
        $paginator->withQueryString();

        if ($window = $this->params->int('on_each_side')) {
            $paginator->onEachSide($window);
        }

        $as = $this->getPaginationResultsKey();
        $items = $paginator->getCollection()->supplement('total_results', $paginator->total());

        return array_merge([
            $as => $items,
            'paginate' => $this->getPaginationData($paginator),
        ], $this->extraOutput($items));
    }

    protected function getPaginationResultsKey()
    {
        return $this->params->get('as', $this->defaultAsKey ?? 'results');
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
            'links'          => $paginator->renderArray(),
        ];
    }
}
