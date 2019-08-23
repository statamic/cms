<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Auth\RoleRepository as BaseRepository;

class RoleRepository extends BaseRepository
{
    public function make()
    {
        return new Role;
    }
}
