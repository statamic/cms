<?php

namespace Statamic\Fields;

class ValuesQueryBuilder
{
    protected $builder;

    public function __construct($builder)
    {
        $this->builder = $builder;
    }

    public function getProxiedInstance()
    {
        return $this->builder;
    }

    public function get()
    {
        $instance = collect($this->builder->get()->toAugmentedCollection())->mapInto(Values::class);

        return new ValuesCollection($instance);
    }

    public function __call($method, $args)
    {
        $this->builder->$method(...$args);

        return $this;
    }
}
