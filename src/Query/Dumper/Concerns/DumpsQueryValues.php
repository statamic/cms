<?php

namespace Statamic\Query\Dumper\Concerns;

trait DumpsQueryValues
{
    protected function dumpQueryArrayValues($array): string
    {
        if (count($array) === 0) {
            return '[]';
        }

        return collect($array)->map(function ($value) {
            return $this->dumpQueryValue($value);
        })->implode(', ');
    }

    protected function dumpQueryValue($value): string
    {
        $this->bindings[] = $value;

        return '?';
    }
}
