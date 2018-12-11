<?php

namespace Statamic\Data\Entries;

use Statamic\API\Entry;
use Statamic\Data\QueryBuilder as BaseQueryBuilder;

class QueryBuilder extends BaseQueryBuilder
{
    protected $collection;

    public function where($column, $value)
    {
        if ($column === 'collection') {
            $this->collection = $value;
            return $this;
        }

        return parent::where($column, $value);
    }

    protected function getBaseItems()
    {
        if ($this->collection) {
            return Entry::whereCollection($this->collection)->values();
        }

        return Entry::all()->values();
    }
}
