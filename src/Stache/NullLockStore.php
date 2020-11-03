<?php

namespace Statamic\Stache;

use Symfony\Component\Lock\BlockingStoreInterface;
use Symfony\Component\Lock\Key;

class NullLockStore implements BlockingStoreInterface
{
    public function save(Key $key)
    {
        //
    }

    public function delete(Key $key)
    {
        //
    }

    public function exists(Key $key)
    {
        //
    }

    public function putOffExpiration(Key $key, float $ttl)
    {
        //
    }

    public function waitAndSave(Key $key)
    {
        //
    }
}
