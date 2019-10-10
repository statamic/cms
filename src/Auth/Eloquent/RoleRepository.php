<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Auth\RoleRepository as BaseRepository;
use Statamic\Contracts\Auth\Role as RoleContract;

class RoleRepository extends BaseRepository
{
    public function make(string $handle = null): RoleContract
    {
        return (new Role)->handle($handle);
    }
}
