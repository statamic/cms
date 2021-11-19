<?php

namespace Statamic\Query;

use Illuminate\Support\Carbon;

abstract class IteratorBuilder extends Builder
{
    protected $randomize = false;

    public function count()
    {
        return $this->getFilteredAndLimitedItems()->count();
    }

    protected function getCountForPagination()
    {
        return $this->getFilteredItems()->count();
    }

    public function get($columns = ['*'])
    {
        $items = $this->getFilteredItems();

        if ($this->randomize) {
            $items = $items->shuffle();
        } elseif ($orderBys = $this->orderBys) {
            $sort = collect($orderBys)->map->toString()->implode('|');
            $items = $items->multisort($sort)->values();
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

    protected function filterWheres($entries)
    {
        foreach ($this->wheres as $where) {
            $method = 'filterWhere'.$where['type'];
            $entries = $this->{$method}($entries, $where);
        }

        return $entries->values();
    }

    protected function filterWhereIn($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            $value = $this->getFilterItemValue($entry, $where['column']);

            return in_array($value, $where['values']);
        });
    }

    protected function filterWhereNotIn($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            $value = $this->getFilterItemValue($entry, $where['column']);

            return ! in_array($value, $where['values']);
        });
    }

    protected function filterWhereBasic($entries, $where)
    {
        return $entries->filter(function ($entry) use ($where) {
            $value = $this->getFilterItemValue($entry, $where['column']);
            $method = 'filterTest'.$this->operators[$where['operator']];

            return $this->{$method}($value, $where['value']);
        });
    }

    protected function filterWhereDate($entries, $where)
    {
        $method = $this->operatorToCarbonMethod($where['operator']);

        return $entries->filter(function ($entry) use ($method, $where) {
            $value = $this->getFilterItemValue($entry, $where['column']);

            if (is_null($value)) {
                return false;
            }

            $value = Carbon::parse($value);
            $value = Carbon::parse($value->format('Y-m-d'));

            return $value->$method($where['value']);
        });
    }

    protected function filterWhereMonth($entries, $where)
    {
        $method = 'filterTest'.$this->operators[$where['operator']];

        return $entries->filter(function ($entry) use ($method, $where) {
            $value = $this->getFilterItemValue($entry, $where['column']);

            if (is_null($value)) {
                return false;
            }

            return $this->{$method}(Carbon::parse($value)->format('m'), $where['value']);
        });
    }

    protected function filterWhereDay($entries, $where)
    {
        $method = 'filterTest'.$this->operators[$where['operator']];

        return $entries->filter(function ($entry) use ($method, $where) {
            $value = $this->getFilterItemValue($entry, $where['column']);

            if (is_null($value)) {
                return false;
            }

            return $this->{$method}(Carbon::parse($value)->format('j'), $where['value']);
        });
    }

    protected function filterWhereYear($entries, $where)
    {
        $method = 'filterTest'.$this->operators[$where['operator']];

        return $entries->filter(function ($entry) use ($method, $where) {
            $value = $this->getFilterItemValue($entry, $where['column']);

            if (is_null($value)) {
                return false;
            }

            return $this->{$method}(Carbon::parse($value)->format('Y'), $where['value']);
        });
    }

    protected function filterWhereTime($entries, $where)
    {
        $method = $this->operatorToCarbonMethod($where['operator']);

        return $entries->filter(function ($entry) use ($method, $where) {
            $value = $this->getFilterItemValue($entry, $where['column']);

            if (is_null($value)) {
                return false;
            }

            $value = Carbon::parse($value);
            $compareValue = $value->copy()->setTimeFromTimeString($where['value']);

            return $value->$method($compareValue);
        });
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

    protected function operatorToCarbonMethod($operator)
    {
        $method = 'eq';

        switch ($operator) {
            case '<>':
            case '!=':
                $method = 'neq';
            break;

            case '>':
                $method = 'gt';
            break;

            case '>=':
                $method = 'gte';
            break;

            case '<':
                $method = 'lt';
            break;

            case '<=':
                $method = 'lte';
            break;
        }

        return $method;
    }

    abstract protected function getBaseItems();

    public function inRandomOrder()
    {
        $this->randomize = true;

        return $this;
    }
}
