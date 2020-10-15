<?php

namespace Statamic\Query;

use Illuminate\Pagination\Paginator;
use InvalidArgumentException;
use Statamic\Contracts\Query\Builder as Contract;
use Statamic\Extensions\Pagination\LengthAwarePaginator;

abstract class Builder implements Contract
{
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

    public function where($column, $operator = null, $value = null)
    {
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

        $this->wheres[] = compact('type', 'column', 'value', 'operator');

        return $this;
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

    public function whereIn($column, $values)
    {
        $this->wheres[] = [
            'type' => 'In',
            'column' => $column,
            'values' => $values,
        ];

        return $this;
    }

    public function whereNotIn($column, $values)
    {
        $this->wheres[] = [
            'type' => 'NotIn',
            'column' => $column,
            'values' => $values,
        ];

        return $this;
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
