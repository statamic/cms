<?php

namespace Statamic\Validation;

use Statamic\Facades\User;

class UniqueUserValue
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        [$except, $column] = array_pad($parameters, 2, null);

        $column ??= $attribute;

        $existing = User::query()
            ->where($column, $value)
            ->first();

        if (! $existing) {
            return true;
        }

        return $except == $existing->id();
    }
}
