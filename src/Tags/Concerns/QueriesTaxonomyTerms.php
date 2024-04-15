<?php

namespace Statamic\Tags\Concerns;

use InvalidArgumentException;

trait QueriesTaxonomyTerms
{
    protected function queryTaxonomyTerms($query, $modifier, $value)
    {
        $values = collect($value);

        $modifier ??= 'any';

        if (in_array($modifier, ['in', 'any'])) {
            $query->whereTaxonomyIn($values->all());
        } elseif (in_array($modifier, ['not_in', 'not'])) {
            $query->whereTaxonomyNotIn($values->all());
        } elseif ($modifier === 'all') {
            $values->each(fn ($value) => $query->whereTaxonomy($value));
        } else {
            throw new InvalidArgumentException(
                'Unknown taxonomy query modifier ['.$modifier.']. Valid values are "any", "not", and "all".'
            );
        }
    }
}
