<?php

namespace Statamic\Query;

use Illuminate\Support\Traits\ForwardsCalls;
use Statamic\Contracts\Query\Builder;

class StatusQueryBuilder implements Builder
{
    use ForwardsCalls;

    const METHODS = [
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
    protected $anyStatus = false;
    protected $hasQueriedStatus = false;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    public function get($columns = ['*'])
    {
        if (! $this->anyStatus && ! $this->hasQueriedStatus) {
            $this->builder->where('status', 'published');
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
            $this->hasQueriedStatus = true;
        }

        $result = $this->forwardCallTo($this->builder, $method, $parameters);

        if ($result === $this->builder) {
            return $this;
        }

        return $result;
    }

    public function whereAnyStatus()
    {
        $this->anyStatus = true;

        return $this;
    }
}
