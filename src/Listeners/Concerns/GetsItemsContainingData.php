<?php

namespace Statamic\Listeners\Concerns;

use Illuminate\Support\LazyCollection;
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
        $collections = [
            LazyCollection::make(function () {
                yield from Entry::query()->lazy();
            }),
            LazyCollection::make(function () {
                yield from Term::query()->lazy();
            }),
            LazyCollection::make(function () {
                yield from GlobalSet::all()->flatMap(fn ($set) => $set->localizations()->values());
            }),
            LazyCollection::make(function () {
                yield from User::query()->lazy();
            }),
        ];

        return LazyCollection::make(function () use ($collections) {
            foreach ($collections as $collection) {
                yield from $collection;
            }
        });
    }
}
