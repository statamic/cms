<?php

namespace Statamic\Contracts\Permissions;

interface UserGroupService
{
    /**
     * Get all the user groups
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAll();

    /**
     * Get a user group by name
     *
     * @param string $group
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public static function get($group);

    /**
     * Get a user group by slug
     *
     * @param string $group
     * @return \Statamic\Contracts\Permissions\UserGroup
     */
    public static function slug($slug);
}
