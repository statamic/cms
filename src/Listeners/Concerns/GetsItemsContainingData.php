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
            ->merge(Term::all()->map->term()->flatMap->localizations()) // See issue #3274
            ->merge(GlobalSet::all()->flatMap->localizations())
            ->merge(User::all());
    }
}
