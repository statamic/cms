<?php

namespace Statamic\Contracts\Data\Repositories;

use Illuminate\Support\Collection;
use Statamic\Contracts\Data\Structures\Structure;

interface StructureRepository
{
    public function all(): Collection;
    public function find($id): ?Structure;
    public function findByHandle($handle): ?Structure;
    public function save(Structure $structure);
}
