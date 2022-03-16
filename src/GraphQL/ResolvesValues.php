<?php

namespace Statamic\GraphQL;

use Statamic\Contracts\Query\Builder;
use Statamic\Fields\Value;

trait ResolvesValues
{
    public function resolveGqlValue($field)
    {
        $value = $this->augmentedValue($field);

        if ($value instanceof Value) {
            $value = $value->value();
        }

        if ($value instanceof Builder) {
            $value = $value->get();
        }

        return $value;
    }

    public function resolveRawGqlValue($field)
    {
        return $this->value($field);
    }
}
