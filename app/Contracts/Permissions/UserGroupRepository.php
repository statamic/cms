<?php

namespace Statamic\Contracts\Permissions;

use Illuminate\Support\Collection;

interface UserGroupRepository
{
    public function all(): Collection;
    public function find($id): ?UserGroup;
}
