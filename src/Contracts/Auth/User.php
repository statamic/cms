<?php

namespace Statamic\Contracts\Auth;

interface User
{
    /**
     * Get or set a user's email address.
     *
     * @param  string|null  $email
     * @return mixed
     */
    public function email($email = null);

    /**
     * Get or set a user's password.
     *
     * @param  string|null  $password
     * @return string
     */
    public function password($password = null);

    public function roles();

    public function assignRole($role);

    public function removeRole($role);

    public function hasRole($role);

    public function groups($groups = null);

    public function addToGroup($group);

    public function removeFromGroup($group);

    public function isInGroup($group);

    public function permissions();

    public function hasPermission($permission);

    public function isSuper();

    public function makeSuper();
}
