<?php

namespace Statamic\Query\Dumper\Concerns;

trait DumpsQueryParts
{
    protected function dumpColumns(): string
    {
        return implode(', ', $this->columns);
    }

    protected function dumpLimits(): string
    {
        if (! $this->limit) {
            return '';
        }

        $limit = ' limit '.$this->limit;

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

        return ' order by '.implode(', ', $orders);
    }
}
