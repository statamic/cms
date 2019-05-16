<?php

namespace Statamic\Eloquent;

use Illuminate\Database\Eloquent\Builder;

abstract class QueryBuilder
{
    protected $builder;

    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    public function __call($method, $args)
    {
        $this->builder->$method(...$args);

        return $this;
    }

    public function get()
    {
        return $this->transform($this->builder->get());
    }

    public function first()
    {
        return $this->get()->first();
    }

    public function paginate()
    {
        $paginator = $this->builder->paginate();

        return $paginator->setCollection(
            $this->transform($paginator->getCollection())
        );
    }

    public function count()
    {
        return $this->builder->count();
    }

    public function where($column, $operator, $value = null)
    {
        $this->builder->where($this->column($column), $operator, $value);

        return $this;
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->builder->orderBy($this->column($column), $direction);

        return $this;
    }

    abstract protected function column($column);
    abstract protected function transform($items);
}
