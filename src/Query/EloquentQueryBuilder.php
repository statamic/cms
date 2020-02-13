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
        $items = $this->builder->get($this->selectableColumns($columns));

        return $this->transform($items, $columns);
    }

    public function first()
    {
        return $this->get()->first();
    }

    public function paginate($perPage, $columns = ['*'])
    {
        $paginator = $this->builder->paginate($perPage, $this->selectableColumns($columns));

        return $paginator->setCollection(
            $this->transform($paginator->getCollection(), $columns)
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

    abstract protected function transform($items, $columns = ['*']);

    protected function selectableColumns($columns = ['*'])
    {
        if (! in_array('*', $columns)) {
            // Any requested columns that aren't actually columns should just be
            // ignored. In actual Laravel Query Builder, you'd get a database
            // exception. Stripping out invalid columns is fine here. They
            // will still be sent through and used for augmentation.
            $model = $this->builder->getModel();
            $schema = $model->getConnection()->getSchemaBuilder()->getColumnListing($model->getTable());
            $selected = array_intersect($schema, $columns);
        }

        return empty($selected) ? ['*'] : $selected;
    }
}
