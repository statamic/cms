<?php

namespace Statamic\Permissions\Redis;

use Statamic\Permissions\File\UserGroupFactory as FileFactory;
use Illuminate\Support\Facades\Cache;

class UserGroupFactory extends FileFactory
{
    protected function getUsers()
    {
        if (! $stached = Cache::get('stache::usergroups/data')) {
            return [];
        }

        if (! $group = $stached->get($this->id)) {
            return [];
        }

        return $group->users();
    }
}
