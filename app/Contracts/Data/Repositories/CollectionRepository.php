<?php

namespace Statamic\Contracts\Data\Repositories;

use Statamic\Data\Entries\Collection;
use Illuminate\Support\Collection as IlluminateCollection;

interface CollectionRepository
{
    public function all(): IlluminateCollection;
    public function find($id): ?Collection;
    public function findByHandle($handle): ?Collection;
    public function findByMount($mount): ?Collection;
    public function make(string $handle = null): Collection;
    public function handles(): IlluminateCollection;
}
