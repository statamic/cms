<?php

namespace Statamic\Contracts\Structures;

interface TreeRepository
{
    public function find($tree): ?Tree;

    public function save(Tree $tree);
}
