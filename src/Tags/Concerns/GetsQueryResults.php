<?php

namespace Statamic\Tags\Concerns;

trait GetsQueryResults
{
    protected function results($query)
    {
        $this->setPaginationParameterPrecedence();

        if ($paginate = $this->parameters->get('paginate')) {
            return $this->paginatedResults($query, $paginate);
        }

        if ($limit = $this->parameters->get('limit')) {
            $query->limit($limit);
        }

        if ($offset = $this->parameters->get('offset')) {
            $query->offset($offset);
        }

        return $query->get();
    }

    protected function setPaginationParameterPrecedence()
    {
        if ($this->parameters->get('paginate') === true) {
            $this->parameters->put('paginate', $this->parameters->get('limit'));
        }
    }

    protected function paginatedResults($query, $perPage)
    {
        if ($offset = $this->parameters->get('offset')) {
            $this->queryPaginationFriendlyOffset($query, $offset);
        }

        return $query->paginate($perPage);
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
