<?php

namespace Statamic\Data;

use InvalidArgumentException;
use Illuminate\Pagination\Paginator;
use Statamic\Extensions\Pagination\LengthAwarePaginator;

abstract class QueryBuilder
{
    protected $limit;
    protected $offset = 0;
    protected $wheres = [];
    protected $orderBy;
    protected $orderDirection;
    protected $operators = [
        '=' => 'Equals',
        'like' => 'Like',
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
        $this->orderBy = $column;
        $this->orderDirection = $direction;

        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        list($value, $operator) = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            list($value, $operator) = [$operator, '='];
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

    public function count()
    {
        return $this->getFilteredAndLimitedItems()->count();
    }

    public function get()
    {
        $items = $this->getFilteredItems();

        if ($this->orderBy) {
            $items = $items->multisort($this->orderBy . ':' . $this->orderDirection);
        }

        return $this->limitItems($items);
    }

    protected function getFilteredItems()
    {
        $items = $this->getBaseItems();

        $items = $this->filterWheres($items);

        return $items;
    }

    protected function getFilteredAndLimitedItems()
    {
        return $this->limitItems($this->getFilteredItems());
    }

    protected function limitItems($items)
    {
        return $items->slice($this->offset, $this->limit);
    }

    abstract protected function getBaseItems();

    public function paginate($perPage = null)
    {
        $page = Paginator::resolveCurrentPage();

        $perPage = $perPage ?: $this->defaultPerPageSize();

        $total = $this->getCountForPagination();

        $results = $total ? $this->forPage($page, $perPage)->get() : $this->collect();

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
    }

    protected function getCountForPagination()
    {
        return $this->getFilteredItems()->count();
    }

    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return app()->makeWith(LengthAwarePaginator::class, compact(
            'items', 'total', 'perPage', 'currentPage', 'options'
        ));
    }

    protected function filterWheres($entries)
    {
        foreach ($this->wheres as $where) {
            $method = 'filterWhere'.$where['type'];
            $entries = $this->{$method}($entries, $where);
        }

        return $entries;
    }

    protected function filterWhereIn($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            $value = $this->getFilterItemValue($entry, $where['column']);
            return in_array($value, $where['values']);
        });
    }

    protected function filterWhereBasic($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            $value = $this->getFilterItemValue($entry, $where['column']);
            $method = 'filterTest' . $this->operators[$where['operator']];
            return $this->{$method}($value, $where['value']);
        });
    }

    protected function filterTestEquals($item, $value)
    {
        return strtolower($item) === strtolower($value);
    }

    protected function filterTestLike($item, $like)
    {
        $like = strtolower($like);

        $pattern = '/' . str_replace(['%', '_'], ['.*', '.'], $like) . '/';

        return preg_match($pattern, strtolower($item));
    }

    protected function getFilterItemValue($item, $column)
    {
        if (is_array($item)) {
            return $item[$column] ?? null;
        }

        return method_exists($item, $column)
            ? $item->{$column}()
            : $item->get($column);
    }

    protected function collect($items = [])
    {
        return collect($items);
    }
}
