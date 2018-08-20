<?php

namespace Statamic\Permissions;

use Statamic\API\Role as RoleAPI;
use Statamic\API\UserGroup as UserGroupAPI;
use Statamic\Contracts\Permissions\Role as RoleContract;
use Statamic\Contracts\Permissions\UserGroup as UserGroupContract;

trait Permissible
{
    public function roles()
    {
        return collect($this->get('roles', []))
            ->map(function ($role) {
                return RoleAPI::find($role);
            })->filter()->keyBy->handle();
    }

    public function assignRole($role)
    {
        $roles = collect(array_wrap($role))->map(function ($role) {
            return is_string($role) ? $role : $role->handle();
        })->all();

        $this->set('roles', array_merge($this->get('roles', []), $roles));

        return $this;
    }

    public function removeRole($role)
    {
        $toBeRemoved = collect(array_wrap($role))->map(function ($role) {
            return is_string($role) ? $role : $role->handle();
        });

        $roles = collect($this->get('roles', []))
            ->diff($toBeRemoved)
            ->values()
            ->all();

        $this->set('roles', $roles);

        return $this;
    }

    public function hasRole($role)
    {
        $role = $role instanceof RoleContract ? $role->handle() : $role;

        return $this->roles()->has($role);
    }

    public function addToGroup($group)
    {
        $groups = collect(array_wrap($group))->map(function ($group) {
            return is_string($group) ? $group : $group->handle();
        })->all();

        $this->set('groups', array_merge($this->get('groups', []), $groups));

        return $this;
    }

    public function removeFromGroup($group)
    {
        $toBeRemoved = collect(array_wrap($group))->map(function ($group) {
            return is_string($group) ? $group : $group->handle();
        });

        $groups = collect($this->get('groups', []))
            ->diff($toBeRemoved)
            ->values()
            ->all();

        $this->set('groups', $groups);

        return $this;
    }

    public function groups($groups = null)
    {
        if ($groups) {
            $this->set('groups', []);
            return $this->addToGroup($groups);
        }

        return collect($this->get('groups', []))
            ->map(function ($group) {
                return UserGroupAPI::find($group);
            })->keyBy->handle();
    }

    public function isInGroup($group)
    {
        $group = $group instanceof UserGroupContract ? $group->handle() : $group;

        return $this->groups()->has($group);
    }

    public function permissions()
    {
        return $this->groups()->flatMap->roles()
            ->merge($this->roles())
            ->flatMap->permissions();
    }

    public function hasPermission($permission)
    {
        return $this->permissions()->contains($permission);
    }

    public function isSuper()
    {
        if ($this->get('super')) {
            return true;
        }

        return null !== $this->groups()->flatMap->roles()
            ->merge($this->roles())
            ->first->isSuper();
    }

    public function makeSuper()
    {
        $this->set('super', true);

        return $this;
    }
}
