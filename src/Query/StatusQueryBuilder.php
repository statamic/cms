<?php

namespace Statamic\Query;

use Illuminate\Support\Traits\ForwardsCalls;
use ReflectionClass;
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
    }

    private function queriesStatus(): bool
    {
        $wheres = null;

        $builder = $this->builder;
        $reflector = new ReflectionClass($builder);

        while (is_null($wheres)) {
            if ($reflector->hasProperty('wheres')) {
                $wheresProperty = $reflector->getProperty('wheres');
                $wheresProperty->setAccessible(true);

                $wheres = $wheresProperty->getValue($builder);
            } elseif ($reflector->hasProperty('builder')) {
                $builderProperty = $reflector->getProperty('builder');
                $builderProperty->setAccessible(true);

                $builder = $builderProperty->getValue($builder);

                $reflector = new ReflectionClass($builder);
            } else {
                return false;
            }
        }

        return collect($wheres)->contains(fn ($where) => array_get($where, 'column') === 'status');
    }
}
