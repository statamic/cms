<?php

namespace Statamic\Eloquent\Auth;

use Statamic\Auth\RoleRepository as BaseRepository;

class RoleRepository extends BaseRepository
{
    public function make()
    {
        return new Role;
    }
}
