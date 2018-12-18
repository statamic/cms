<?php

namespace Statamic\Data\Entries;

use Statamic\API\Entry;
use Statamic\Data\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{
    protected $collection;

    public function where($column, $operator = null, $value = null)
    {
        if ($column === 'collection') {
            $this->collection = $operator;
            return $this;
        }

        return parent::where($column, $operator, $value);
    }

    protected function getBaseItems()
    {
        if ($this->collection) {
            return Entry::whereCollection($this->collection)->values();
        }

        return Entry::all()->values();
    }

    protected function collect($items = [])
    {
        return collect_entries($items);
    }
}
