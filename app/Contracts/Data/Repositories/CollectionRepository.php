<?php

namespace Statamic\Contracts\Data\Repositories;

use Statamic\Data\Entries\Collection;
use Illuminate\Support\Collection as IlluminateCollection;

interface CollectionRepository
{
    public function all(): IlluminateCollection;
    public function findByHandle($handle): ?Collection;
}
