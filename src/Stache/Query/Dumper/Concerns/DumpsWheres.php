<?php

namespace Statamic\Stache\Query\Dumper\Concerns;

use Illuminate\Support\Str;
use Statamic\Stache\Query\Builder;

trait DumpsWheres
{
    protected function dumpBasic($where): string
    {
        return $where['column'].' '.strtoupper($where['operator']).' '.$this->dumpQueryValue($where['value'] ?? null);
    }

    protected function dumpArrayWhere($keyword, $where): string
    {
        return $where['column'].' '.$keyword.' ('.$this->dumpQueryArrayValues($where['values'] ?? []).')';
    }

    protected function dumpSimpleOperatorWhere($where): string
    {
        return $where['column'].' '.$where['operator'].$this->dumpQueryValue($where['value'] ?? null);
    }

    protected function dumpIn($where): string
    {
        return $this->dumpArrayWhere('IN', $where);
    }

    protected function dumpNotIn($where): string
    {
        return $this->dumpArrayWhere('NOT IN', $where);
    }

    protected function dumpNull($where): string
    {
        return $where['column'].' IS NULL';
    }

    protected function dumpNotNull($where): string
    {
        return $where['column'].' IS NOT NULL';
    }

    protected function dumpDatePartMethod($datePart, $where): string
    {
        return 'DATEPART('.$datePart.', '.$where['column'].') = '.$this->dumpQueryValue($where['value'] ?? null);
    }

    protected function dumpMonth($where): string
    {
        return $this->dumpDatePartMethod('MONTH', $where);
    }

    protected function dumpDay($where): string
    {
        return $this->dumpDatePartMethod('DAY', $where);
    }

    protected function dumpYear($where): string
    {
        return $this->dumpDatePartMethod('YEAR', $where);
    }

    protected function dumpTime($where): string
    {
        return $this->dumpDatePartMethod('TIMESTAMP', $where);
    }

    protected function dumpBetween($where): string
    {
        $valueOne = $this->dumpQueryValue($where['values'][0] ?? null);
        $valueTwo = $this->dumpQueryValue($where['values'][1] ?? null);
        $column = $where['column'];

        return $column.' BETWEEN '.$valueOne.' AND '.$valueTwo;
    }

    protected function dumpNotBetween($where): string
    {
        $valueOne = $this->dumpQueryValue($where['values'][0] ?? null);
        $valueTwo = $this->dumpQueryValue($where['values'][1] ?? null);
        $column = $where['column'];

        return $column.' NOT BETWEEN '.$valueOne.' AND '.$valueTwo;
    }

    protected function dumpColumn($where): string
    {
        return $where['column'].' = '.$where['value'];
    }

    protected function dumpNested($where): string
    {
        $query = $where['query'] ?? null;

        if (! $query instanceof Builder) {
            return '';
        }

        return '('.$query->dumpStacheQuery($this->bindings).')';
    }

    protected function dumpDate($where)
    {
        return $this->dumpSimpleOperatorWhere($where);
    }

    protected function dumpJsonMethod($where): string
    {
        $jsonMethod = strtoupper(Str::snake($where['type']));

        if (isset($where['values'])) {
            $valueString = $this->dumpQueryArrayValues($where['values']);
        } else {
            $valueString = $this->dumpQueryValue($where['value'] ?? null);
        }

        return $jsonMethod.'('.$where['column'].', '.$valueString.')';
    }

    protected function dumpWhere($isFirst, $where): string
    {
        $dumpedWhere = '';

        if (! $isFirst) {
            $dumpedWhere = strtoupper($where['boolean']).' ';
        }

        $type = $where['type'];

        if (Str::startsWith($type, 'Json')) {
            $dumpedWhere .= $this->dumpJsonMethod($where);
        } else {
            $whereMethod = 'dump'.ucfirst($type);

            if (method_exists($this, $whereMethod)) {
                $dumpedWhere .= $this->{$whereMethod}($where);
            } else {
                // Fail-safe to dump "something".
                $dumpedWhere .= strtoupper($type);
            }
        }

        return $dumpedWhere;
    }

    protected function dumpWheres(): string
    {
        if (count($this->wheres) === 0) {
            return '';
        }

        $parts = [];

        for ($i = 0; $i < count($this->wheres); $i++) {
            $parts[] = $this->dumpWhere($i === 0, $this->wheres[$i]);
        }

        return "\n".'WHERE '.implode(' ', $parts);
    }
}