<?php

namespace Statamic\Stache\Indexes\Terms;

use Statamic\Facades\Term;
use Statamic\Stache\Indexes\Value as Index;

class Value extends Index
{
    public function getItemValue($item)
    {
        // The augmented `entries_count` value only considers published entries, so
        // when filtering (e.g. via the `min_count` param on the taxonomy tag) we
        // need to resolve the same published-only count for consistency.
        if ($this->name === 'entries_count') {
            return Term::entriesCount($item, 'published');
        }

        return parent::getItemValue($item);
    }

    public function getItems()
    {
        // There's an association for every entry that uses a term, so most terms have
        // many. They all make the same term, so it only needs making once per site.
        $associations = [];

        foreach ($this->store->index('associations')->items() as $association) {
            if ($association) {
                $associations[$association['site'].'::'.$association['slug']] = $association;
            }
        }

        $associatedItems = [];

        foreach ($associations as $association) {
            $term = Term::make($value = $association['slug'])
                ->taxonomy($this->store->childKey())
                ->set('title', $value)
                ->in($association['site']);

            $associatedItems[$term->locale().'::'.$term->slug()] = $this->getItemValue($term);
        }

        return collect($associatedItems)
            ->merge(parent::getItems())
            ->all();
    }
}
