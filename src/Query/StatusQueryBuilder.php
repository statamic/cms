<?php

namespace Statamic\Query;

use Illuminate\Support\Traits\ForwardsCalls;
use Statamic\Contracts\Query\Builder;
use Statamic\Support\Arr;

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

    const RESULT_METHODS = [
        'count',
        'exists',
        'pluck',
        'paginate',
        'find',
        'firstOrFail',
        'firstOr',
        'sole',
        'min',
        'max',
        'sum',
        'avg',
        'average',
        'chunk',
        'lazy',
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
        $this->applyFallbackStatus();

        return $this->builder->get($columns);
    }

    private function applyFallbackStatus(): void
    {
        if ($this->queryFallbackStatus) {
            $this->builder->whereStatus($this->fallbackStatus);
            $this->queryFallbackStatus = false;
        }
    }

    public function first()
    {
        return $this->get()->first();
    }

    public function __call($method, $parameters)
    {
        if ((in_array($method, self::METHODS) && in_array(Arr::first($parameters), ['status', 'published'])) || $method === 'whereStatus') {
            $this->queryFallbackStatus = false;
        }

        if (in_array($method, self::RESULT_METHODS)) {
            $this->applyFallbackStatus();
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
