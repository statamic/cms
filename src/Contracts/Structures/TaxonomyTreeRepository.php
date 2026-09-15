<?php

namespace Statamic\Contracts\Structures;

interface TaxonomyTreeRepository
{
    public function find(string $handle): ?Tree;

    public function save(Tree $tree);

    public function delete(Tree $tree);
}
