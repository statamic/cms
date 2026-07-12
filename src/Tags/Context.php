<?php

namespace Statamic\Tags;

use Statamic\Fields\Value;

class Context extends ArrayAccessor
{
    public function raw($key, $default = null)
    {
        $value = parent::get($key, $default);

        return $value instanceof Value ? $value->raw() : $value;
    }

    public function value($key, $default = null)
    {
        $value = parent::get($key, $default);

        return $value instanceof Value ? $value->value() : $value;
    }
}
