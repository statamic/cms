<?php

namespace Statamic\Contracts\Data\Repositories;

use Statamic\Data\Entries\EntryCollection;

interface EntryRepository
{
    public function all(): EntryCollection;
    public function whereCollection(string $handle): EntryCollection;
    public function whereInCollection(array $handles): EntryCollection;
}
