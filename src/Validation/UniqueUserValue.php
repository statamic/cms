<?php

namespace Statamic\Validation;

use Statamic\Facades\User;

class UniqueUserValue
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        [$except] = array_pad($parameters, 1, null);

        $existing = User::query()
            ->where($attribute, $value)
            ->first();

        if (! $existing) {
            return true;
        }

        return $except == $existing->id();
    }
}
