<?php

namespace Statamic\Tags\Concerns;

trait GetsQueryResults
{
    protected function results($query)
    {
        $this->setPaginationParameterPrecedence();

        if ($paginate = $this->params->get('paginate')) {
            return $this->paginatedResults($query, $paginate);
        }

        if ($limit = $this->params->get('limit')) {
            $query->limit($limit);
        }

        if ($offset = $this->params->get('offset')) {
            $query->offset($offset);
        }

        return $query->get();
    }

    protected function setPaginationParameterPrecedence()
    {
        if ($this->params->get('paginate') === true) {
            $this->params->put('paginate', $this->params->get('limit'));
        }
    }

    protected function paginatedResults($query, $perPage)
    {
        if ($offset = $this->params->get('offset')) {
            $this->queryPaginationFriendlyOffset($query, $offset);
        }

        return tap($query->paginate($perPage), function ($paginator) {
            $paginator->setCollection($paginator->getCollection()->values());
        });
    }

    protected function queryPaginationFriendlyOffset($query, $offset)
    {
        $offsetIds = (clone $query)
            ->limit($offset)
            ->get('id')
            ->map->id()
            ->all();

        $query->whereNotin('id', $offsetIds);
    }
}
