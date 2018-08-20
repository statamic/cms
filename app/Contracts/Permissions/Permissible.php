<?php

namespace Statamic\Contracts\Permissions;

interface Permissible
{
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
