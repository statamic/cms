<?php

namespace Statamic\Permissions\Redis;

use Statamic\API\Config;
use Statamic\Permissions\File\UserGroup as FileUserGroup;

class UserGroup extends FileUserGroup
{
    /**
     * Get the array that should be written to file for this group.
     *
     * @return array
     */
    protected function toSavableArray()
    {
        $arr = parent::toSavableArray();

        // If writing user files has been disabled, we will be
        // relying on the users that are stored in the Stache.
        if (! Config::get('users.redis_write_file')) {
            unset($arr['users']);
        }

        return $arr;
    }
}
