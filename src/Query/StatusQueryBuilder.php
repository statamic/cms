<?php

namespace Statamic\Query;

use Illuminate\Support\Traits\ForwardsCalls;
use Statamic\Contracts\Query\Builder;

class StatusQueryBuilder implements Builder
{
    use ForwardsCalls;

    protected $builder;
    protected $anyStatus = false;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    public function get($columns = ['*'])
    {
        if (! $this->anyStatus && ! $this->queriesStatus()) {
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

    private function queriesStatus(): bool
    {
        return collect($this->wheres())->contains(
            fn ($where) => in_array(array_get($where, 'column'), ['status', 'published'])
        );
    }
}
