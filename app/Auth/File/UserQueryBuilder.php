<?php

namespace Statamic\Auth\File;

use Statamic\API\User;
use Statamic\Data\QueryBuilder;

class UserQueryBuilder extends QueryBuilder
{
    protected function getBaseItems()
    {
        return User::all()->values();
    }
}