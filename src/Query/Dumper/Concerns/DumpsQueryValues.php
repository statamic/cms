<?php

namespace Statamic\Query\Dumper\Concerns;

use DateTimeInterface;
use Illuminate\Support\Carbon;

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
        if ($value instanceof DateTimeInterface) {
            $value = Carbon::instance($value)->setTimezone(config('app.timezone'));
        }

        $this->bindings[] = $value;

        return '?';
    }
}
