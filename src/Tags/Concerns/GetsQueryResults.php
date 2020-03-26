<?php

namespace Statamic\Tags\Concerns;

use Statamic\Support\Arr;

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
        $paginate = Arr::get($this->parameters, 'paginate');
        $limit = Arr::get($this->parameters, 'limit');
        $offset = Arr::get($this->parameters, 'offset');

        if ($paginate === true) {
            $paginate = $limit;
        }

        return compact('paginate', 'limit', 'offset');
    }
}
