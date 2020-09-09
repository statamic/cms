<?php

namespace Statamic\Validation;

use Statamic\Facades\Entry;

class UniqueEntryValue
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        [$collection, $except, $site] = array_pad($parameters, 3, null);

        $query = Entry::query();

        if ($collection) {
            $query->where('collection', $collection);
        }

        if ($site) {
            $query->where('site', $site);
        }

        $existing = $query
            ->where($attribute, $value)
            ->first();

        if (! $existing) {
            return true;
        }

        return $except == $existing->id();
    }
}
