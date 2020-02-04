<?php

namespace Statamic\Query;

use Illuminate\Database\Eloquent\Builder;

abstract class EloquentQueryBuilder
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

    public function get($columns = ['*'])
    {
        return $this->transform($this->builder->get($columns));
    }

    public function first()
    {
        return $this->get()->first();
    }

    public function paginate($perPage, $columns = ['*'])
    {
        $paginator = $this->builder->paginate($perPage, $columns);

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

    protected function column($column)
    {
        return $column;
    }

    abstract protected function transform($items);
}
