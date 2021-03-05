<?php

namespace Statamic\Contracts\Structures;

interface CollectionTreeRepository
{
    public function find(string $handle, string $site): ?Tree;

    public function save(Tree $tree);
}
