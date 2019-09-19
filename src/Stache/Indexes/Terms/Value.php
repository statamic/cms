<?php

namespace Statamic\Stache\Indexes\Terms;

use Statamic\Facades\Term;
use Statamic\Stache\Indexes\Value as Index;

class Value extends Index
{
    public function getItems()
    {
        $associatedItems = $this->store->index('associations')->items()
            ->mapWithKeys(function ($association) {
                $term = Term::make($value = $association['value'])
                    ->set('title', $value)
                    ->taxonomy($this->store->childKey());

                return [$term->slug() => $this->getItemValue($term)];
            });

        return $associatedItems
            ->merge(parent::getItems())
            ->all();
    }
}
