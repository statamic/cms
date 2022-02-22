<?php

namespace Statamic\Query;

use Closure;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Statamic\Contracts\Query\Builder as Contract;
use Statamic\Extensions\Pagination\LengthAwarePaginator;

abstract class Builder implements Contract
{
    protected $columns;
    protected $limit;
    protected $offset = 0;
    protected $wheres = [];
    protected $orderBys = [];
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

    public function select($columns = ['*'])
    {
        $this->columns = $columns;

        return $this;
    }

    public function limit($value)
    {
        $this->limit = $value;

        return $this;
    }

    public function offset($value)
    {
        $this->offset = max(0, $value);

        return $this;
    }

    public function forPage($page, $perPage = null)
    {
        $perPage = $perPage ?: $this->defaultPerPageSize();

        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }

    protected function defaultPerPageSize()
    {
        return 15; // TODO get from config.
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->orderBys[] = new OrderBy($column, $direction);

        return $this;
    }

    abstract public function inRandomOrder();

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        if ($column instanceof Closure && is_null($operator)) {
            return $this->whereNested($column, $boolean);
        }

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        $type = 'Basic';
        $this->wheres[] = compact('type', 'column', 'value', 'operator', 'boolean');

        return $this;
    }

    public function whereNested(Closure $callback, $boolean = 'and')
    {
        call_user_func($callback, $query = $this->forNestedWhere());

        return $this->addNestedWhereQuery($query, $boolean);
    }

    public function forNestedWhere()
    {
        $newBuilder = clone $this;
        $newBuilder->wheres = [];

        return $newBuilder;
    }

    public function addNestedWhereQuery($query, $boolean = 'and')
    {
        if (count($query->wheres)) {
            $type = 'Nested';
            $this->wheres[] = compact('type', 'query', 'boolean');
        }

        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'or');
    }

    protected function addArrayOfWheres($column, $boolean, $method = 'where')
    {
        return $this->whereNested(function ($query) use ($column, $method, $boolean) {
            foreach ($column as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $query->{$method}(...array_values($value));
                } else {
                    $query->$method($key, '=', $value, $boolean);
                }
            }
        }, $boolean);
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

    protected function invalidOperator($operator)
    {
        return ! in_array(strtolower($operator), array_keys($this->operators), true);
    }

    public function whereIn($column, $values, $boolean = 'and')
    {
        $this->wheres[] = [
            'type' => 'In',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean,
        ];

        return $this;
    }

    public function orWhereIn($column, $values)
    {
        $this->wheres[] = [
            'type' => 'In',
            'column' => $column,
            'values' => $values,
            'boolean' => 'or',
        ];

        return $this;
    }

    public function whereNotIn($column, $values, $boolean = 'and')
    {
        $this->wheres[] = [
            'type' => 'NotIn',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean,
        ];

        return $this;
    }

    public function orWhereNotIn($column, $values)
    {
        $this->wheres[] = [
            'type' => 'NotIn',
            'column' => $column,
            'values' => $values,
            'boolean' => 'or',
        ];

        return $this;
    }

    public function whereNull($column, $boolean = 'and', $not = false)
    {
        $this->wheres[] = [
            'type' => ($not ? 'Not' : '').'Null',
            'column' => $column,
            'boolean' => $boolean,
        ];

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
        $values = array_slice(Arr::flatten($values), 0, 2);

        if (count($values) != 2) {
            throw new InvalidArgumentException('Values should be an array of length 2');
        }

        $this->wheres[] = [
            'type' => ($not ? 'Not' : '').'Between',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean,
        ];

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

    public function whereColumn($column, $operator = null, $value = null, $boolean = 'and')
    {
        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        $type = 'Column';
        $this->wheres[] = compact('type', 'column', 'value', 'operator', 'boolean');

        return $this;
    }

    public function orWhereColumn($column, $operator = null, $value = null)
    {
        return $this->whereColumn($column, $operator, $value, 'or');
    }

    public function find($id, $columns = ['*'])
    {
        return $this->where('id', $id)->get($columns)->first();
    }

    public function first()
    {
        return $this->get()->first();
    }

    public function paginate($perPage = null, $columns = ['*'])
    {
        $page = Paginator::resolveCurrentPage();

        $perPage = $perPage ?: $this->defaultPerPageSize();

        $total = $this->getCountForPagination();

        $results = $total ? $this->forPage($page, $perPage)->get($columns) : $this->collect();

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    abstract protected function getCountForPagination();

    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return app()->makeWith(LengthAwarePaginator::class, compact(
            'items', 'total', 'perPage', 'currentPage', 'options'
        ));
    }

    protected function collect($items = [])
    {
        return collect($items);
    }

    abstract public function count();

    abstract public function get($columns = ['*']);

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

    protected function filterTestEquals($item, $value)
    {
        return strtolower($item) === strtolower($value);
    }

    protected function filterTestNotEquals($item, $value)
    {
        if (is_string($item)) {
            return strtolower($item) !== strtolower($value);
        }

        return $item !== $value;
    }

    protected function filterTestLessThan($item, $value)
    {
        return $item < $value;
    }

    protected function filterTestGreaterThan($item, $value)
    {
        return $item > $value;
    }

    protected function filterTestLessThanOrEqualTo($item, $value)
    {
        return $item <= $value;
    }

    protected function filterTestGreaterThanOrEqualTo($item, $value)
    {
        return $item >= $value;
    }

    protected function filterTestLike($item, $like)
    {
        $pattern = '/^'.str_replace(['%', '_'], ['.*', '.'], preg_quote($like, '/')).'$/im';

        if (is_array($item)) {
            $item = json_encode($item);
        }

        return preg_match($pattern, $item);
    }

    protected function filterTestNotLike($item, $like)
    {
        return ! $this->filterTestLike($item, $like);
    }

    protected function filterTestLikeRegex($item, $pattern)
    {
        return preg_match("/{$pattern}/im", $item);
    }

    protected function filterTestNotLikeRegex($item, $pattern)
    {
        return ! $this->filterTestLikeRegex($item, $pattern);
    }
}
