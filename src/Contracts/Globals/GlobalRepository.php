<?php

namespace Statamic\Contracts\Globals;

use Statamic\Globals\GlobalCollection;

interface GlobalRepository
{
    public function all(): GlobalCollection;

    public function find($id): ?GlobalSet;

    public function findByHandle($handle): ?GlobalSet;

    public function save($global);
}
