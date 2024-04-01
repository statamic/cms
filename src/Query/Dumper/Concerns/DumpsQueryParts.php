<?php

namespace Statamic\Query\Dumper\Concerns;

use Statamic\Contracts\Query\Builder;
use Statamic\Stache\Query\Builder as StacheQueryBuilder;
use Statamic\Support\Str;

trait DumpsQueryParts
{
    public function getTableName(Builder $class)
    {
        if (method_exists($class, 'getTableNameForFakeQuery')) {
            return $class->getTableNameForFakeQuery();
        }

        if ($class instanceof StacheQueryBuilder) {
            return Str::of(class_basename($class))
                ->before('QueryBuilder')
                ->lower()
                ->plural()
                ->toString();
        }

        return get_class($class);
    }

    protected function dumpColumns(): string
    {
        $columns = '*';

        if ($this->columns != null) {
            $columns = implode(', ', $this->columns);
        }

        return $columns;
    }

    protected function dumpLimits(): string
    {
        if (! $this->limit) {
            return '';
        }

        $limit = "\n".'limit '.$this->limit;

        if ($this->offset) {
            $limit .= ' offset '.$this->offset;
        }

        return $limit;
    }

    protected function dumpOrderBys(): string
    {
        if (count($this->orderBys) === 0) {
            return '';
        }

        $orders = [];

        foreach ($this->orderBys as $orderBy) {
            if (! $orderBy->sort) {
                continue;
            }

            $orders[] = $orderBy->sort.' '.$orderBy->direction;
        }

        return "\n".'order by '.implode(', ', $orders);
    }
}
