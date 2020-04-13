<?php

namespace Statamic\Contracts\Structures;

use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Contracts\Structures\Structure;

interface StructureRepository
{
    public function all(): Collection;
    public function find($id): ?Structure;
    public function findByHandle($handle): ?Structure;
    public function save(Structure $structure);
    public function make(string $handle = null): Structure;
}
