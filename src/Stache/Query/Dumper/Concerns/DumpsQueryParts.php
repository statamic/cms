<?php

namespace Statamic\Stache\Query\Dumper\Concerns;

use Statamic\Stache\Stores\Store;
use Statamic\Support\Str;

trait DumpsQueryParts
{
    protected function dumpTableNameFromStore(Store $store): string
    {
        return Str::of(class_basename($store))
            ->before('Store')
            ->lower()
            ->toString();
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

        $limit = "\n".'LIMIT '.$this->limit;

        if ($this->offset) {
            $limit .= ' OFFSET '.$this->offset;
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

            $orders[] = $orderBy->sort.' '.strtoupper($orderBy->direction);
        }

        return "\n".'ORDER BY '.implode(', ', $orders);
    }
}
