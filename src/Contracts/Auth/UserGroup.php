<?php

namespace Statamic\Contracts\Auth;

interface UserGroup
{
    public function id(): string;

    public function title(string $title = null);

    public function handle(string $slug = null);

    public function users();

    public function queryUsers();

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
