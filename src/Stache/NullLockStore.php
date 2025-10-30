<?php

namespace Statamic\Stache;

use Symfony\Component\Lock\BlockingStoreInterface;
use Symfony\Component\Lock\Key;

class NullLockStore implements BlockingStoreInterface
{
    public function save(Key $key): void
    {
        //
    }

    public function delete(Key $key): void
    {
        //
    }

    public function exists(Key $key): bool
    {
        return false;
    }

    public function putOffExpiration(Key $key, float $ttl): void
    {
        //
    }

    public function waitAndSave(Key $key): void
    {
        //
    }
}
