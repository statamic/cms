<?php

namespace Statamic\Stache\Repositories;

use Statamic\Contracts\Structures\CollectionTree as TreeContract;
use Statamic\Stache\Stache;
use Statamic\Structures\CollectionTree;

class CollectionTreeRepository extends NavTreeRepository
{
    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('collection-trees');
    }

    public static function bindings()
    {
        return [
            TreeContract::class => CollectionTree::class,
        ];
    }
}
