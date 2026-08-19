<?php

namespace Statamic\Contracts\CP\ResourceIndex;

interface GroupRepository
{
    public function find(string $resourceIndex): ?array;

    public function save(string $resourceIndex, array $groups): void;

    public function delete(string $resourceIndex): void;
}
