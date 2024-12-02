<?php

namespace Statamic\Listeners\Concerns;

use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Term;
use Statamic\Facades\User;

trait GetsItemsContainingData
{
    /**
     * Get items containing data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getItemsContainingData()
    {
        return collect()
            ->merge(Entry::all())
            ->merge(Term::all())
            ->merge(GlobalSet::all()->flatMap(fn ($set) => $set->localizations()->values()))
            ->merge(User::all());
    }
}
