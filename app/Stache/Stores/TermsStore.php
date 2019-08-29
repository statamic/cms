<?php

namespace Statamic\Stache\Stores;

use Statamic\API\Taxonomy;

class TermsStore extends AggregateStore
{
    protected $childStore = TaxonomyTermsStore::class;

    public function key()
    {
        return 'terms';
    }

    public function discoverStores()
    {
        return Taxonomy::handles()->map(function ($handle) {
            return $this->store($handle);
        });
    }
}
