<?php

namespace Statamic\Validation;

use Statamic\Facades\Term;

class UniqueTermValue
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        [$taxonomy, $except, $site] = array_pad($parameters, 3, null);

        $query = Term::query();

        if ($taxonomy) {
            $query->where('taxonomy', $taxonomy);
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
