<?php

namespace Statamic\Contracts\Permissions;

interface Permissible
{
    /**
     * Get the user's ID
     *
     * @return string
     */
    public function id();

    /**
     * Get the roles for the user
     *
     * @return \Illuminate\Support\Collection
     */
    public function roles();

    /**
     * Does the user have a given role?
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role);

    /**
     * Does the user have a given permission?
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission);

    /**
     * Get all the user's permissions
     *
     * @return mixed
     */
    public function permissions();

    /**
     * Is this a super user?
     *
     * @return bool
     */
    public function isSuper();

    /**
     * Get or set the groups this user belongs to
     *
     * @param array|null $groups
     * @return \Illuminate\Support\Collection
     */
    public function groups($groups = null);

    /**
     * Does this user belong to a given group?
     *
     * @param string $group
     * @return bool
     */
    public function inGroup($group);
}
