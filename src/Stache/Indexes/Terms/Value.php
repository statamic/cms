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
        $associatedItems = $this->store->index('associations')->items()
            ->filter()
            ->mapWithKeys(function ($association) {
                $term = Term::make($value = $association['slug'])
                    ->taxonomy($this->store->childKey())
                    ->set('title', $value)
                    ->in($association['site']);

                return [$term->locale().'::'.$term->slug() => $this->getItemValue($term)];
            });

        return $associatedItems
            ->merge(parent::getItems())
            ->all();
    }
}
