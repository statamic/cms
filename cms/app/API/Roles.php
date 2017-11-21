<?php

namespace Statamic\API;

/**
 * @deprecated since 2.1
 */
class Roles
{
    /**
     * Get all the roles
     *
     * @deprecated since 2.1
     */
    public static function all()
    {
        \Log::notice('Roles::all() is deprecated. Use Role::all()');

        return Role::all();
    }

    /**
     * Get a role
     *
     * @param string $id
     * @return \Statamic\Contracts\Permissions\Role
     * @deprecated since 2.1
     */
    public static function get($id)
    {
        \Log::notice('Roles::get() is deprecated. Use Role::find()');

        return Role::find($id);
    }

    /**
     * Get a role by slug
     *
     * @param  string $slug
     * @return \Statamic\Contracts\Permissions\Role
     * @deprecated since 2.1
     */
    public static function slug($slug)
    {
        \Log::notice('Roles::slug() is deprecated. Use Role::whereHandle()');

        return Role::whereHandle($slug);
    }
}
