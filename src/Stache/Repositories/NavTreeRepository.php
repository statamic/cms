<?php

namespace Statamic\Stache\Repositories;

use Statamic\Contracts\Structures\NavTreeRepository as Contract;
use Statamic\Contracts\Structures\Tree;
use Statamic\Stache\Stache;

class NavTreeRepository implements Contract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('nav-trees');
    }

    public function find(string $handle, string $site): ?Tree
    {
        return $this->store->getItem("$handle::$site");
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
        return [];
    }
}
