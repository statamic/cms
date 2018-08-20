<?php

namespace Statamic\Contracts\Permissions;

use Illuminate\Support\Collection;

interface RoleRepository
{
    public function all(): Collection;
    public function find(string $id): ?Role;
    public function exists(string $id): bool;
}
