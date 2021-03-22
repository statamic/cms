<?php

namespace Statamic\Validation;

use Statamic\Facades\Entry;

class UniqueEntryValue
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        [$collection, $except, $site, $uri, $slug] = array_pad($parameters, 5, null);

        $query = Entry::query();

        if ($collection) {
            $query->where('collection', $collection);
        }

        if ($site) {
            $query->where('site', $site);
        }

        if ($uri) {
            $query->where('uri', str_replace($slug, '', $uri));
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
