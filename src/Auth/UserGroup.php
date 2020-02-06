<?php

namespace Statamic\Auth;

use Statamic\Facades;
use Statamic\Facades\User;
use Statamic\Facades\Role as RoleAPI;
use Illuminate\Support\Collection;
use Statamic\Contracts\Auth\Role;
use Statamic\Contracts\Auth\Permissible;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;

class UserGroup implements UserGroupContract
{
    protected $title;
    protected $handle;
    protected $originalHandle;
    protected $users;
    protected $originalUsers;
    protected $roles;

    public function __construct()
    {
        $this->users = collect();
        $this->roles = collect();
    }

    public function title(string $title = null)
    {
        if (is_null($title)) {
            return $this->title;
        }

        $this->title = $title;

        return $this;
    }

    public function id(): string
    {
        return $this->handle();
    }

    public function handle(string $handle = null)
    {
        if (is_null($handle)) {
            return $this->handle;
        }

        if (! $this->originalHandle) {
            $this->originalHandle = $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function originalHandle()
    {
        return $this->originalHandle;
    }

    public function users($users = null)
    {
        if (is_null($users)) {
            return $this->users;
        }

        $this->users = collect();

        foreach ($users as $user) {
            $this->addUser($user);
        }

        return $this;
    }

    public function originalUsers()
    {
        return $this->originalUsers;
    }

    public function resetOriginalUsers()
    {
        $this->originalUsers = collect($this->users);

        return $this;
    }

    public function addUser($user)
    {
        if (is_string($user)) {
            $user = User::find($user);
        }

        $this->users->put($user->id(), $user);

        return $this;
    }

    public function removeUser($user)
    {
        if ($user instanceof UserContract) {
            $user = $user->id();
        }

        $this->users->forget($user);

        return $this;
    }

    public function hasUser($user): bool
    {
        if ($user instanceof UserContract) {
            $user = $user->id();
        }

        return $this->users->has($user);
    }

    public function roles($roles = null)
    {
        if (is_null($roles)) {
            return $this->roles;
        }

        $this->roles = collect();

        foreach ($roles as $role) {
            $this->assignRole($role);
        }

        return $this;
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = RoleAPI::find($role);
        }

        $this->roles->put($role->handle(), $role);

        return $this;
    }

    public function removeRole($role)
    {
        if ($role instanceof Role) {
            $role = $role->handle();
        }

        $this->roles->forget($role);

        return $this;
    }

    public function hasRole($role): bool
    {
        if ($role instanceof Role) {
            $role = $role->handle();
        }

        return $this->roles->has($role);
    }

    public function hasPermission($permission)
    {
        return $this->roles->reduce(function ($carry, $role) {
            return $carry->merge($role->permissions());
        }, collect())->contains($permission);
    }

    public function isSuper(): bool
    {
        return $this->hasPermission('super');
    }

    public function save()
    {
        Facades\UserGroup::save($this);

        return $this;
    }

    public function delete()
    {
        Facades\UserGroup::delete($this);

        return $this;
    }

    public function editUrl()
    {
        return cp_route('user-groups.edit', $this->handle());
    }

    public function deleteUrl()
    {
        return cp_route('user-groups.destroy', $this->handle());
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\UserGroup::{$method}(...$parameters);
    }
}
