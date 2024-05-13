<?php

namespace Statamic\Support;

use Statamic\Fields\Value;

class Dumper
{
    public static function resolve($values)
    {
        if (is_array($values)) {
            $values = collect($values)
                ->map(fn ($value) => $value instanceof Value ? $value->resolve() : $value)
                ->all();
        }

        return $values;
    }
}
