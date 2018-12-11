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

    abstract protected function transform($items);
}
