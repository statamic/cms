<?php

namespace Statamic\Query;

use Closure;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Statamic\Contracts\Query\Builder;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Support\Arr;
use Statamic\Support\Str;

abstract class EloquentQueryBuilder implements Builder
{
    protected $builder;
    protected $columns;

    public function __construct(EloquentBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function __call($method, $args)
    {
        if (Str::startsWith($method, ['where', 'orWhere', 'firstWhere'])) {
            if (is_string($args[0])) {
                $args[0] = $this->column($args[0]);
            }
            if ($args[0] instanceof Closure) {
                $builder = app(static::class);
                $args[0]($builder);
                $args[0] = fn ($query) => $query->setQuery($builder->getQuery());
            }
        }
        $this->builder->$method(...$args);

        return $this;
    }

    public function select($columns = ['*'])
    {
        $this->columns = $columns;

        return $this;
    }

    public function get($columns = ['*'])
    {
        $columns = $this->columns ?? $columns;

        $items = $this->builder->get($this->selectableColumns($columns));

        $items = $this->transform($items, $columns);

        if (($first = $items->first()) && method_exists($first, 'selectedQueryColumns')) {
            $items->each->selectedQueryColumns($columns);
        }

        return $items;
    }

    public function first()
    {
        return $this->get()->first();
    }

    public function paginate($perPage = null, $columns = [], $pageName = 'page', $page = null)
    {
        $paginator = $this->builder->paginate($perPage, $this->selectableColumns($columns), $pageName, $page);

        $paginator = app()->makeWith(LengthAwarePaginator::class, [
            'items' => $paginator->items(),
            'total' => $paginator->total(),
            'perPage' => $paginator->perPage(),
            'currentPage' => $paginator->currentPage(),
            'options' => $paginator->getOptions(),
        ]);

        return $paginator->setCollection(
            $this->transform($paginator->getCollection(), $columns)
        );
    }

    public function getCountForPagination()
    {
        return $this->builder->getCountForPagination();
    }

    public function count()
    {
        return $this->builder->count();
    }

    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        if (func_num_args() === 2) {
            [$second, $operator] = [$operator, '='];
        }

        $this->builder->whereColumn($this->column($first), $operator, $this->column($second), $boolean);

        return $this;
    }

    public function orWhereColumn($first, $operator = null, $second = null)
    {
        return $this->whereColumn($first, $operator, $second, 'or');
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->builder->orderBy($this->column($column), $direction);

        return $this;
    }

    public function getQuery()
    {
        return $this->builder->getQuery();
    }

    public function setQuery($query)
    {
        $this->builder->setQuery($query);
    }

    protected function column($column)
    {
        return $column;
    }

    abstract protected function transform($items, $columns = ['*']);

    protected function selectableColumns($columns = ['*'])
    {
        $columns = Arr::wrap($columns);

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
