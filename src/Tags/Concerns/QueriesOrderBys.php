<?php

namespace Statamic\Tags\Concerns;

use Statamic\Query\OrderBy;
use Statamic\Support\Arr;

trait QueriesOrderBys
{
    protected $orderBys;

    public function queryOrderBys($query)
    {
        $orderBys = $this->parseOrderBys();

        if ($orderBys->map->sort->contains('random')) {
            return $query->inRandomOrder();
        }

        $orderBys->each(function ($orderBy) use ($query) {
            $query->orderBy($orderBy->sort, $orderBy->direction);
        });
    }

    protected function parseOrderBys()
    {
        if ($orderBys = $this->preParsedOrderBys()) {
            return $orderBys;
        }

        $piped = Arr::getFirst($this->params, ['order_by', 'sort'], $this->defaultOrderBy());

        return collect(explode('|', $piped ?? ''))->filter()->map(function ($orderBy) {
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
