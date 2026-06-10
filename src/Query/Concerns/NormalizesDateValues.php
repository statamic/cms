<?php

namespace Statamic\Query\Concerns;

use DateTimeInterface;
use Illuminate\Support\Carbon;

trait NormalizesDateValues
{
    protected function normalizeWhereDateValue($value)
    {
        return $value instanceof DateTimeInterface
            ? Carbon::instance($value)->setTimezone(config('app.timezone'))
            : $value;
    }
}
