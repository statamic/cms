<?php

namespace Statamic\API;

/**
 * @deprecated since 2.1
 */
class UserGroups
{
    /**
     * Get all the groups
     *
     * @deprecated since 2.1
     */
    public static function all()
    {
        \Log::notice('UserGroups::all() is deprecated. Use UserGroup::all()');

        return UserGroup::all();
    }

    /**
     * Get a user group by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Permissions\UserGroup
     * @deprecated since 2.1
     */
    public static function get($id)
    {
        \Log::notice('UserGroups::get() is deprecated. Use UserGroup::find()');

        return UserGroup::find($id);
    }

    /**
     * Get a group by slug
     *
     * @param  string $slug
     * @return \Statamic\Contracts\Permissions\UserGroup
     * @deprecated since 2.1
     */
    public static function slug($slug)
    {
        \Log::notice('UserGroups::slug() is deprecated. Use UserGroup::whereHandle()');

        return UserGroup::whereHandle($slug);
    }

    /**
     * Get the user groups for a given user
     *
     * @param string|\Statamic\Contracts\Permissions\Permissible $user
     * @return \Illuminate\Support\Collection
     * @deprecated since 2.1
     */
    public static function forUser($user)
    {
        \Log::notice('UserGroups::forUser() is deprecated. Use UserGroup::whereUser()');

        return UserGroup::whereUser($user);
    }
}
