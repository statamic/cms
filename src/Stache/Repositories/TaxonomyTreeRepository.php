<?php

namespace Statamic\Stache\Repositories;

use Statamic\Contracts\Structures\TaxonomyTree as TreeContract;
use Statamic\Contracts\Structures\TaxonomyTreeRepository as Contract;
use Statamic\Contracts\Structures\Tree;
use Statamic\Facades\Site;
use Statamic\Stache\Stache;
use Statamic\Structures\TaxonomyTree;

class TaxonomyTreeRepository implements Contract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('taxonomy-trees');
    }

    public function find(string $handle): ?Tree
    {
        return $this->store->getItem($handle.'::'.Site::default()->handle());
    }

    public function save(Tree $tree)
    {
        $this->store->save($tree);

        return true;
    }

    public function delete(Tree $tree)
    {
        $this->store->delete($tree);

        return true;
    }

    public static function bindings()
    {
        return [
            TreeContract::class => TaxonomyTree::class,
        ];
    }
}
