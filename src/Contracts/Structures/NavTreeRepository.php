<?php

namespace Statamic\Contracts\Structures;

interface NavTreeRepository
{
    public function find(string $handle, string $site): ?Tree;

    public function save(Tree $tree);

    public function delete(Tree $tree);
}
