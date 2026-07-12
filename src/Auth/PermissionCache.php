<?php

namespace Statamic\Auth;

class PermissionCache
{
    protected $items = [];

    public function get(string $user)
    {
        return $this->items[$user] ?? null;
    }

    public function put(string $user, $permissions)
    {
        $this->items[$user] = $permissions;
    }

    public function clear()
    {
        $this->items = [];
    }
}
