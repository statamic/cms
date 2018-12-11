<?php

namespace Statamic\Auth\Eloquent;

class UserQueryBuilder
{
    protected $builder;

    public function __construct($builder)
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

    protected function transform($items)
    {
        return collect_users($items)->map(function ($model) {
            return User::fromModel($model);
        });
    }
}
