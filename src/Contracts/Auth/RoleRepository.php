<?php

namespace Statamic\Contracts\Auth;

use Illuminate\Support\Collection;

interface RoleRepository
{
    public function all(): Collection;

    public function find(string $id): ?Role;

    public function exists(string $id): bool;

    public function make(string $handle = null): Role;
}
