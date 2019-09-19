<?php

namespace Statamic\Contracts\Auth;

use Illuminate\Support\Collection;

interface UserGroup
{
    public function id(): string;
    public function title(string $title = null);
    public function handle(string $slug = null);
    public function users($users = null);
    public function addUser($user);
    public function removeUser($user);
    public function hasUser($user): bool;
    public function roles($roles = null);
    public function hasRole($role): bool;
    public function assignRole($role);
    public function removeRole($role);
    public function hasPermission($permission);
    public function isSuper(): bool;
    public function save();
    public function delete();
}
