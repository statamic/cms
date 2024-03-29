<?php

namespace Statamic\Stache\Query\Dumper\Concerns;

use Carbon\Carbon;

trait DumpsQueryValues
{
    protected function dumpQueryArrayValues($array): string
    {
        if (count($array) === 0) {
            return '[]';
        }

        if (! $this->dumpActualValues) {
            return implode(', ', array_fill(0, count($array), '?'));
        }

        return collect($array)->map(function ($value) {
            return $this->dumpQueryValue($value);
        })->implode(', ');
    }

    protected function dumpQueryValue($value): string
    {
        if (! $this->dumpActualValues) {
            return '?';
        }

        if (is_string($value)) {
            return "'$value'";
        }

        if (is_bool($value)) {
            if ($value === true) {
                return '1';
            }

            return '0';
        }

        if (is_null($value)) {
            return 'NULL';
        }

        if ($value instanceof Carbon) {
            return $value->toIso8601String();
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_object($value)) {
            return '{object}';
        }

        return '{value}';
    }
}
