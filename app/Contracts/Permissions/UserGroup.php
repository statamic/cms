<?php

namespace Statamic\Contracts\Permissions;

use Statamic\Contracts\CP\Editable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;

interface UserGroup extends Jsonable, Arrayable, Editable
{
    /**
     * Get or set the ID
     *
     * @param string|null $id
     * @return mixed
     */
    public function id($id = null);

    /**
     * Get or set the UUID
     *
     * @param string|null $uuid
     * @return mixed
     * @deprecated
     */
    public function uuid($uuid = null);

    /**
     * Get or set the title
     *
     * @param string|null $title
     * @return mixed
     */
    public function title($title = null);

    /**
     * Get or set the slug
     *
     * @param string|null $title
     * @return mixed
     */
    public function slug($slug = null);

    /**
     * Get or set the users
     *
     * @param array|null $users
     * @return mixed
     */
    public function users($users = null);

    /**
     * Add a user to the group
     *
     * @param string|\Statamic\Contracts\Data\User $user
     * @return mixed
     */
    public function addUser($user);

    /**
     * Remove a user from the group
     *
     * @param string|\Statamic\Contracts\Data\User $user
     * @return mixed
     */
    public function removeUser($user);

    /**
     * Does a given user exist in this group?
     *
     * @param string|\Statamic\Contracts\Permissions\Permissible $user
     * @return mixed
     */
    public function hasUser($user);

    /**
     * Get or set the roles
     *
     * @param array|null $roles
     * @return mixed
     */
    public function roles($roles = null);

    /**
     * Does this group have a given role?
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role);

    /**
     * Add a role to this group
     *
     * @param string|Role $role
     * @return mixed
     */
    public function addRole($role);

    /**
     * Remove a role from this group
     *
     * @param $role
     * @return mixed
     */
    public function removeRole($role);

    /**
     * Does this group have a given permission?
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission);

    /**
     * Does this group have super permissions?
     *
     * @return bool
     */
    public function isSuper();

    /**
     * Save this group
     *
     * @return mixed
     */
    public function save();

    /**
     * Delete this group
     *
     * @return mixed
     */
    public function delete();
}
