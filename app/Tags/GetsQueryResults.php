<?php

namespace Statamic\Tags;

use Statamic\API\Arr;

trait GetsQueryResults
{
    protected function results($query, $params = null)
    {
        $params = $this->parsePaginationParameters($params ?? $this->parameters);

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

    protected function parsePaginationParameters($params)
    {
        $paginate = Arr::get($params, 'paginate');
        $limit = Arr::get($params, 'limit');
        $offset = Arr::get($params, 'offset');

        if ($paginate === true) {
            $paginate = $limit;
        }

        return compact('paginate', 'limit', 'offset');
    }
}
