<?php

namespace Statamic\Stache\Repositories;

use Statamic\Contracts\Structures\CollectionTree as TreeContract;
use Statamic\Contracts\Structures\Tree;
use Statamic\Facades\Stache as StacheFacade;
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

    public function save(Tree $tree)
    {
        $result = parent::save($tree);

        StacheFacade::store('entries')
            ->store($tree->collection()->handle())
            ->resolveIndexes()
            ->each->update();

        return $result;
    }
}
