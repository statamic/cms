<?php

namespace Statamic\Contracts\Auth;

use Illuminate\Support\Collection;

interface UserGroupRepository
{
    public function all(): Collection;

    public function find($id): ?UserGroup;
}
