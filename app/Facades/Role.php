<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\RoleRepository;

class Role extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RoleRepository::class;
    }
}
