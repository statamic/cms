<?php

namespace Statamic\Query;

use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use Statamic\Contracts\Query\Builder;
use Statamic\Extensions\Pagination\LengthAwarePaginator;
use Statamic\Facades\Blink;
use Statamic\Support\Arr;

abstract class EloquentQueryBuilder implements Builder
{
    protected $builder;
    protected $columns;

    protected $operators = [
        '=' => 'Equals',
        '<>' => 'NotEquals',
        '!=' => 'NotEquals',
        'like' => 'Like',
        'not like' => 'NotLike',
        'regexp' => 'LikeRegex',
        'not regexp' => 'NotLikeRegex',
        '>' => 'GreaterThan',
        '<' => 'LessThan',
        '>=' => 'GreaterThanOrEqualTo',
        '<=' => 'LessThanOrEqualTo',
    ];

    public function __construct(EloquentBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function __call($method, $args)
    {
        $response = $this->builder->$method(...$args);

        return $response instanceof EloquentBuilder ? $this : $response;
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

    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
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

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        if ($column instanceof Closure && is_null($operator)) {
            return $this->whereNested($column, $boolean);
        }

        if (strtolower($operator) == 'like') {
            $grammar = $this->builder->getConnection()->getQueryGrammar();
            $this->builder->whereRaw('LOWER('.$grammar->wrap($this->column($column)).') LIKE ?', strtolower($value), $boolean);

            return $this;
        }

        $this->builder->where($this->column($column), $operator, $value, $boolean);

        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'or');
    }

    public function whereColumn($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $this->builder->whereColumn($this->column($column), $operator, $this->column($value), $boolean);

        return $this;
    }

    public function whereIn($column, $values, $boolean = 'and')
    {
        $this->builder->whereIn($this->column($column), $values, $boolean);

        return $this;
    }

    public function orWhereIn($column, $values)
    {
        return $this->whereIn($column, $values, 'or');
    }

    public function whereNotIn($column, $values, $boolean = 'and')
    {
        $this->builder->whereNotIn($this->column($column), $values, $boolean);

        return $this;
    }

    public function orWhereNotIn($column, $values)
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    public function whereJsonContains($column, $values, $boolean = 'and')
    {
        $this->builder->whereJsonContains($this->column($column), $values, $boolean);

        return $this;
    }

    public function orWhereJsonContains($column, $values)
    {
        return $this->whereJsonContains($column, $values, 'or');
    }

    public function whereJsonDoesntContain($column, $values, $boolean = 'and')
    {
        $this->builder->whereJsonDoesntContain($this->column($column), $values, $boolean);

        return $this;
    }

    public function orWhereJsonDoesntContain($column, $values)
    {
        return $this->whereJsonDoesntContain($column, $values, 'or');
    }

    public function whereJsonLength($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $this->builder->whereJsonLength($this->column($column), $operator, $value, $boolean);

        return $this;
    }

    public function orWhereJsonLength($column, $operator, $value = null)
    {
        return $this->whereJsonLength($column, $operator, $value, 'or');
    }

    public function whereNull($column, $boolean = 'and', $not = false)
    {
        $this->builder->whereNull($this->column($column), $boolean, $not);

        return $this;
    }

    public function orWhereNull($column)
    {
        return $this->whereNull($column, 'or');
    }

    public function whereNotNull($column, $boolean = 'and')
    {
        return $this->whereNull($column, $boolean, true);
    }

    public function orWhereNotNull($column)
    {
        return $this->whereNotNull($column, 'or');
    }

    public function whereBetween($column, $values, $boolean = 'and', $not = false)
    {
        $this->builder->whereBetween($this->column($column), $values, $boolean, $not);

        return $this;
    }

    public function orWhereBetween($column, $values)
    {
        return $this->whereBetween($column, $values, 'or');
    }

    public function whereNotBetween($column, $values, $boolean = 'and')
    {
        return $this->whereBetween($column, $values, 'or', true);
    }

    public function orWhereNotBetween($column, $values)
    {
        return $this->whereNotBetween($column, $values, 'or');
    }

    public function whereDate($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        if (! ($value instanceof DateTimeInterface)) {
            $value = Carbon::parse($value);
        }

        $this->builder->whereDate($this->column($column), $operator, $value, $boolean);

        return $this;
    }

    public function orWhereDate($column, $operator, $value = null)
    {
        return $this->whereDate($column, $operator, $value, 'or');
    }

    public function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $this->builder->whereMonth($this->column($column), $operator, $value, $boolean);

        return $this;
    }

    public function orWhereMonth($column, $operator, $value = null)
    {
        return $this->whereMonth($column, $operator, $value, 'or');
    }

    public function whereDay($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $this->builder->whereDay($this->column($column), $operator, $value, $boolean);

        return $this;
    }

    public function orWhereDay($column, $operator, $value = null)
    {
        return $this->whereDay($column, $operator, $value, 'or');
    }

    public function whereYear($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $this->builder->whereYear($this->column($column), $operator, $value, $boolean);

        return $this;
    }

    public function orWhereYear($column, $operator, $value = null)
    {
        return $this->whereYear($column, $operator, $value, 'or');
    }

    public function whereTime($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $this->builder->whereTime($this->column($column), $operator, $value, $boolean);

        return $this;
    }

    public function orWhereTime($column, $operator, $value = null)
    {
        return $this->whereTime($column, $operator, $value, 'or');
    }

    public function whereNested(Closure $callback, $boolean = 'and')
    {
        $query = app(static::class);
        $callback($query);

        $this->builder->getQuery()->addNestedWhereQuery($query->builder->getQuery(), $boolean);

        return $this;
    }

    protected function addArrayOfWheres($column, $boolean, $method = 'where')
    {
        $this->whereNested(function ($query) use ($column, $method, $boolean) {
            foreach ($column as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $query->{$method}(...array_values($value));
                } else {
                    $query->$method($this->column($key), '=', $value, $boolean);
                }
            }
        }, $boolean);

        return $this;
    }

    public function when($value, $callback, $default = null)
    {
        if ($value) {
            return $callback($this, $value) ?: $this;
        }

        if ($default) {
            return $default($this, $value) ?: $this;
        }

        return $this;
    }

    public function tap($callback)
    {
        return $this->when(true, $callback);
    }

    public function unless($value, $callback, $default = null)
    {
        if (! $value) {
            return $callback($this, $value) ?: $this;
        }

        if ($default) {
            return $default($this, $value) ?: $this;
        }

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
        $columns = Arr::wrap($columns);

        if (! in_array('*', $columns)) {
            // Any requested columns that aren't actually columns should just be
            // ignored. In actual Laravel Query Builder, you'd get a database
            // exception. Stripping out invalid columns is fine here. They
            // will still be sent through and used for augmentation.
            $model = $this->builder->getModel();
            $table = $model->getTable();

            $schema = Blink::once("eloquent-schema-{$table}", function () use ($model, $table) {
                return $model->getConnection()->getSchemaBuilder()->getColumnListing($table);
            });

            $selected = array_intersect($schema, $columns);
        }

        return empty($selected) ? ['*'] : $selected;
    }

    public function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }

    protected function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, array_keys($this->operators)) &&
             ! in_array($operator, ['=', '<>', '!=']);
    }

    /**
     * Chunk the results of the query.
     *
     * @param  int  $count
     * @return bool
     */
    public function chunk($count, callable $callback)
    {
        $this->enforceOrderBy();

        $page = 1;

        do {
            // We'll execute the query for the given page and get the results. If there are
            // no results we can just break and return from here. When there are results
            // we will call the callback with the current chunk of these results here.
            $results = $this->forPage($page, $count)->get();

            $countResults = $results->count();

            if ($countResults == 0) {
                break;
            }

            // On each chunk result set, we will pass them to the callback and then let the
            // developer take care of everything within the callback, which allows us to
            // keep the memory low for spinning through large result sets for working.
            if ($callback($results, $page) === false) {
                return false;
            }

            unset($results);

            $page++;
        } while ($countResults == $count);

        return true;
    }

    /**
     * Query lazily, by chunks of the given size.
     *
     * @param  int  $chunkSize
     * @return \Illuminate\Support\LazyCollection
     *
     * @throws \InvalidArgumentException
     */
    public function lazy($chunkSize = 1000)
    {
        if ($chunkSize < 1) {
            throw new InvalidArgumentException('The chunk size should be at least 1');
        }

        $this->enforceOrderBy();

        return LazyCollection::make(function () use ($chunkSize) {
            $page = 1;

            while (true) {
                $results = $this->forPage($page++, $chunkSize)->get();

                foreach ($results as $result) {
                    yield $result;
                }

                if ($results->count() < $chunkSize) {
                    return;
                }
            }
        });
    }

    /**
     * Add a generic "order by" clause if the query doesn't already have one.
     *
     * @return void
     */
    protected function enforceOrderBy()
    {
        if (empty($this->builder->getQuery()->orders) && empty($this->builder->getQuery()->unionOrders)) {
            $this->orderBy($this->builder->getModel()->getQualifiedKeyName(), 'asc');
        }
    }
}
