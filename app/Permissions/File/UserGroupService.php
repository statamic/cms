<?php

namespace Statamic\Permissions\File;

use Statamic\Contracts\Permissions\UserGroupService as UserGroupServiceContract;

class UserGroupService implements UserGroupServiceContract
{
    /**
     * Get all the user groups
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAll()
    {
        return user_cache()->getAllGroups();
    }

    /**
     * Get a user group by name
     *
     * @param string $group
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public static function get($group)
    {
        return user_cache()->getGroup($group);
    }

    /**
     * Get a user group by slug
     *
     * @param string $slug
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public static function slug($slug)
    {
        return self::getAll()->filter(function ($group) use ($slug) {
            return $group->slug() === $slug;
        })->first();
    }
}
