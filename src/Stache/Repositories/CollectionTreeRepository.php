<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;

class CollectionTreeRepository extends NavTreeRepository
{
    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('collection-trees');
    }
}
