<?php

namespace Statamic\Tags\Query;

use Statamic\API\Arr;
use Statamic\Query\OrderBy;

trait HasOrderBys
{
    protected $orderBys;

    public function queryOrderBys($query)
    {
        $this->parseOrderBys()->each(function ($orderBy) use ($query) {
            $query->orderBy($orderBy->sort, $orderBy->direction);
        });
    }

    protected function parseOrderBys()
    {
        if ($orderBys = $this->preParsedOrderBys()) {
            return $orderBys;
        }

        $piped = Arr::getFirst($this->parameters, ['order_by', 'sort'], $this->defaultOrderBy());

        return collect(explode('|', $piped))->filter()->map(function ($orderBy) {
            return OrderBy::parse($orderBy);
        });
    }

    protected function preParsedOrderBys()
    {
        return $this->orderBys ?? false;
    }

    protected function defaultOrderBy()
    {
        return null;
    }
}
