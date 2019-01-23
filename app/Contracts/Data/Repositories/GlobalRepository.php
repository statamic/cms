<?php

namespace Statamic\Contracts\Data\Repositories;

use Statamic\Data\Globals\GlobalCollection;
use Statamic\Contracts\Data\Globals\GlobalSet;

interface GlobalRepository
{
    public function all(): GlobalCollection;
    public function find($id): ?GlobalSet;
    public function findByHandle($handle): ?GlobalSet;
    public function save($global);
}
