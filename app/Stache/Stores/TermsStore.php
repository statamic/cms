<?php

namespace Statamic\Stache\Stores;

class TermsStore extends AggregateStore
{
    protected $childStore = TaxonomyTermsStore::class;

    public function key()
    {
        return 'terms';
    }
}
