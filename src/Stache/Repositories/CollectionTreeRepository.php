<?php

namespace Statamic\Stache\Repositories;

use Statamic\Contracts\Structures\Tree;
use Statamic\Stache\Stache;

class CollectionTreeRepository extends NavTreeRepository
{
    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('collection-trees');
    }

    public function save(Tree $tree)
    {
        parent::save($tree);

        $collection = $tree->collection();

        if ($collection->orderable()) {
            $this->stache->store('entries')->store($collection->handle())->index('order')->update();
        }

        return true;
    }
}
