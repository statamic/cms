<?php

namespace Statamic\Query;

use Illuminate\Support\Traits\ForwardsCalls;
use Statamic\Contracts\Query\Builder;

class OrderedQueryBuilder implements Builder
{
    use ForwardsCalls;

    protected $builder;
    protected $order;
    protected $ordered = false;
    protected $limit;
    protected $offset;

    public function __construct(Builder $builder, $order = [])
    {
        $this->builder = $builder;
        $this->order = $order;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->ordered = true;

        return $this->forwardCallTo($this->builder, 'orderBy', func_get_args());
    }

    public function get($columns = ['*'])
    {
        $results = $this->builder->get($columns);

        if (! $this->ordered) {
            $results = $this->performFallbackOrdering($results);
        }

        return $results
            ->when($this->limit, fn ($results) => $results->take($this->limit))
            ->when($this->offset, fn ($results) => $results->skip($this->offset))
            ->values();
    }

    public function __call($method, $parameters)
    {
        $result = $this->forwardCallTo($this->builder, $method, $parameters);

        if ($result === $this->builder) {
            return $this;
        }

        return $result;
    }

    private function performFallbackOrdering($results)
    {
        return $results->sort(function ($a, $b) {
            $a = array_search($a['id'], $this->order);
            $b = array_search($b['id'], $this->order);

            if ($a === false && $b === false) {
                return 0;
            } elseif ($a === false) {
                return 1;
            } elseif ($b === false) {
                return -1;
            }

            return $a <=> $b;
        })->values();
    }

    public function limit($value)
    {
        $this->limit = $value;

        return $this;
    }

    public function offset($value)
    {
        $this->offset = max(0, $value);

        return $this;
    }
}
