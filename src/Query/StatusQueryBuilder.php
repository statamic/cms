<?php

namespace Statamic\Query;

use Illuminate\Support\Traits\ForwardsCalls;
use Statamic\Contracts\Query\Builder;

class StatusQueryBuilder implements Builder
{
    use ForwardsCalls;

    const METHODS = [
        'where',
        'whereIn',
        'whereNotIn',
        'whereNull',
        'whereNotNull',
        'orWhere',
        'orWhereIn',
        'orWhereNotIn',
        'orWhereNull',
        'orWhereNotNull',
    ];

    protected $builder;
    protected $queryFallbackStatus = true;
    protected $fallbackStatus;

    public function __construct(Builder $builder, $status = 'published')
    {
        $this->builder = $builder;
        $this->fallbackStatus = $status;
    }

    public function get($columns = ['*'])
    {
        if ($this->queryFallbackStatus) {
            $this->builder->where('status', $this->fallbackStatus);
        }

        return $this->builder->get($columns);
    }

    public function first()
    {
        return $this->get()->first();
    }

    public function __call($method, $parameters)
    {
        if (in_array($method, self::METHODS) && in_array(array_first($parameters), ['status', 'published'])) {
            $this->queryFallbackStatus = false;
        }

        $result = $this->forwardCallTo($this->builder, $method, $parameters);

        if ($result === $this->builder) {
            return $this;
        }

        return $result;
    }

    public function whereAnyStatus()
    {
        $this->queryFallbackStatus = false;

        return $this;
    }
}
