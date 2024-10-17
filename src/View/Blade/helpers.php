<?php

namespace Statamic\View\Blade;

use Statamic\Fields\Value;
use Statamic\Fields\Values;

function value(mixed $value): mixed
{
    if ($value instanceof Value) {
        $value = $value->value();
    } elseif ($value instanceof Values) {
        $value = $value->all();
    }

    return $value;
}
