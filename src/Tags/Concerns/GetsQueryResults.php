<?php

namespace Statamic\Tags\Concerns;

trait GetsQueryResults
{
    protected function results($query)
    {
        $params = $this->parsePaginationParameters();

        if ($paginate = $params['paginate']) {
            return $query->paginate($paginate);
        }

        if ($limit = $params['limit']) {
            $query->limit($limit);
        }

        if ($offset = $params['offset']) {
            $query->offset($offset);
        }

        return $query->get();
    }

    protected function parsePaginationParameters()
    {
        $paginate = $this->parameters->get('paginate');
        $limit = $this->parameters->get('limit');
        $offset = $this->parameters->get('offset');

        if ($paginate === true) {
            $paginate = $limit;
        }

        return compact('paginate', 'limit', 'offset');
    }
}
