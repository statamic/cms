<?php

namespace Statamic\Contracts\Data\Repositories;

use Statamic\Contracts\Data\Entries\Entry;
use Statamic\Data\Entries\EntryCollection;

interface EntryRepository
{
    public function all(): EntryCollection;
    public function whereCollection(string $handle): EntryCollection;
    public function whereInCollection(array $handles): EntryCollection;
    public function find($id): ?Entry;
    public function findByUri(string $uri): ?Entry;
}
