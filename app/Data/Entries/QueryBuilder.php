<?php

namespace Statamic\Data\Entries;

use Statamic\API\Entry;
use Statamic\Data\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{
    protected $collections;

    public function where($column, $operator = null, $value = null)
    {
        if ($column === 'collection') {
            $this->collections[] = $operator;
            return $this;
        }

        return parent::where($column, $operator, $value);
    }

    public function whereIn($column, $values)
    {
        if (in_array($column, ['collection', 'collections'])) {
            $this->collections = array_merge($this->collections ?? [], $values);
            return $this;
        }

        return parent::whereIn($column, $values);
    }

    protected function getBaseItems()
    {
        if ($this->collections) {
            return collect_entries($this->collections)->flatMap(function ($collection) {
                return Entry::whereCollection($collection);
            })->values();
        }

        return Entry::all()->values();
    }

    protected function collect($items = [])
    {
        return collect_entries($items);
    }
}
