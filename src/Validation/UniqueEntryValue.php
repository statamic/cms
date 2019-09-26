<?php

namespace Statamic\Validation;

use Statamic\Facades\Entry;

class UniqueEntryValue
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        [$collection, $except] = array_pad($parameters, 2, null);

        $query = Entry::query();

        if ($collection) {
            $query->where('collection', $collection);
        }

        $existing = $query
            ->where($attribute, $value)
            ->first();

        if (! $existing) {
            return true;
        }

        return $except === $existing->id();
    }
}