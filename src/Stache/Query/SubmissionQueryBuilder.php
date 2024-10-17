<?php

namespace Statamic\Stache\Query;

use Statamic\Contracts\Forms\SubmissionQueryBuilder as QueryBuilderContract;
use Statamic\Data\DataCollection;
use Statamic\Facades;
use Statamic\Query\OrderBy;

class SubmissionQueryBuilder extends Builder implements QueryBuilderContract
{
    protected $forms;

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column === 'form') {
            $this->forms[] = $operator;

            return $this;
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    public function whereIn($column, $values, $boolean = 'and')
    {
        if (in_array($column, ['form', 'forms'])) {
            $this->forms = array_merge($this->forms ?? [], $values);

            return $this;
        }

        return parent::whereIn($column, $values, $boolean);
    }

    public function orderBy($column, $direction = 'asc')
    {
        if ($column === 'datestamp') {
            $column = 'date';
        }

        $this->orderBys[] = new OrderBy($column, $direction);

        return $this;
    }

    protected function collect($items = [])
    {
        return DataCollection::make($items);
    }

    protected function getFilteredKeys()
    {
        $forms = empty($this->forms)
            ? Facades\Form::all()->map->handle()
            : $this->forms;

        return empty($this->wheres)
            ? $this->getKeysFromForms($forms)
            : $this->getKeysFromFormsWithWheres($forms, $this->wheres);
    }

    protected function getKeysFromForms($forms)
    {
        return collect($forms)->flatMap(function ($form) {
            $keys = $this->store->store($form)->paths()->keys();

            return collect($keys)->map(function ($key) use ($form) {
                return "{$form}::{$key}";
            });
        });
    }

    protected function getKeysFromFormsWithWheres($forms, $wheres)
    {
        return collect($wheres)->reduce(function ($ids, $where) use ($forms) {
            $keys = $where['type'] == 'Nested'
                ? $this->getKeysFromFormsWithWheres($forms, $where['query']->wheres)
                : $this->getKeysFromFormsWithWhere($forms, $where);

            return $this->intersectKeysFromWhereClause($ids, $keys, $where);
        });
    }

    protected function getKeysFromFormsWithWhere($forms, $where)
    {
        $items = collect($forms)->flatMap(function ($form) use ($where) {
            return $this->getWhereColumnKeysFromStore($form, $where);
        });

        $method = 'filterWhere'.$where['type'];

        return $this->{$method}($items, $where)->keys();
    }

    protected function getOrderKeyValuesByIndex()
    {
        $forms = empty($this->forms)
            ? Facades\Form::all()->map->handle()
            : $this->forms;

        // First, we'll get the values from each index grouped by form
        $keys = collect($forms)->map(function ($form) {
            $store = $this->store->store($form);

            return collect($this->orderBys)->mapWithKeys(function ($orderBy) use ($form, $store) {
                $items = $store->index($orderBy->sort)
                    ->items()
                    ->mapWithKeys(function ($item, $key) use ($form) {
                        return ["{$form}::{$key}" => $item];
                    })->all();

                return [$orderBy->sort => $items];
            });
        });

        // Then, we'll merge all the corresponding index values together from each form.
        return $keys->reduce(function ($carry, $form) {
            foreach ($form as $sort => $values) {
                $carry[$sort] = array_merge($carry[$sort] ?? [], $values);
            }

            return $carry;
        }, collect());
    }

    protected function getWhereColumnKeyValuesByIndex($column)
    {
        $forms = empty($this->forms)
            ? Facades\Form::all()->map->handle()
            : $this->forms;

        return collect($forms)->flatMap(function ($form) use ($column) {
            return $this->getWhereColumnKeysFromStore($form, ['column' => $column]);
        });
    }
}
