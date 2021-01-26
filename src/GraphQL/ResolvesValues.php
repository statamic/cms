<?php

namespace Statamic\GraphQL;

use Statamic\Fields\Value;

trait ResolvesValues
{
    public function resolveGqlValue($field)
    {
        $value = $this->augmentedValue($field);

        if ($value instanceof Value) {
            $value = $value->value();
        }

        return $value;
    }

    public function resolveRawGqlValue($field)
    {
        return $this->value($field);
    }
}
