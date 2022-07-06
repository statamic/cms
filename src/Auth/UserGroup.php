<?php

namespace Statamic\Auth;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Auth\Role;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\HasAugmentedData;
use Statamic\Events\UserGroupDeleted;
use Statamic\Events\UserGroupSaved;
use Statamic\Facades;
use Statamic\Facades\Role as RoleAPI;

abstract class UserGroup implements UserGroupContract, Augmentable, ArrayAccess, Arrayable
{
    protected $title;
    protected $handle;
    protected $originalHandle;
    protected $roles;

    use HasAugmentedData;

    public function __construct()
    {
        $this->roles = collect();
    }

    public function title(string $title = null)
    {
        if (func_num_args() === 0) {
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

    public function users()
    {
        return $this->queryUsers()->get();
    }

    abstract public function queryUsers();

    public function hasUser($user): bool
    {
        return $user->isInGroup($this);
    }

    public function roles($roles = null)
    {
        if (func_num_args() === 0) {
            return $this->roles;
        }

        $this->roles = collect();

        foreach ($roles ?? [] as $role) {
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

        UserGroupSaved::dispatch($this);

        return true;
    }

    public function delete()
    {
        Facades\UserGroup::delete($this);

        UserGroupDeleted::dispatch($this);

        return true;
    }

    public function showUrl()
    {
        return cp_route('user-groups.show', $this->handle());
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

    public function augmentedArrayData()
    {
        return [
            'title' => $this->title(),
            'handle' => $this->handle(),
        ];
    }
}
