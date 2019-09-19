<?php

namespace Statamic\Auth\File;

use Statamic\Auth\RoleRepository as BaseRepository;

class RoleRepository extends BaseRepository
{
    public function make()
    {
        return new Role;
    }
}