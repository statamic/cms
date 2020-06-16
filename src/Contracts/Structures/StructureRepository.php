<?php

namespace Statamic\Contracts\Structures;

use Illuminate\Support\Collection;

interface StructureRepository
{
    public function all(): Collection;

    public function find($id): ?Structure;

    public function findByHandle($handle): ?Structure;

    public function save(Structure $structure);
}
