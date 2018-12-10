<?php

namespace Statamic\Auth\Eloquent;

use Statamic\API\Role;
use Statamic\API\UserGroup;
use Statamic\Auth\User as BaseUser;
use Illuminate\Support\Facades\Hash;
use Statamic\Contracts\Auth\User as UserContract;

class User extends BaseUser
{
    protected $model;
    protected $roles;
    protected $groups;

    public static function fromModel(Model $model)
    {
        return tap(new static, function ($user) use ($model) {
            $user->model($model);
        });
    }

    public function model(Model $model = null)
    {
        if (is_null($model)) {
            return $this->model;
        }

        $this->model = $model;

        return $this;
    }

    /**
     * Get or set all the data for the current locale
     *
     * @param array|null $data
     * @return $this|array
     */
    public function data($data = null)
    {
        if (is_null($data)) {
            return $this->model()->attributesToArray();
        }

        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    public function id($id = null)
    {
        return $this->model()->getKey();
    }

    public function email($email = null)
    {
        return $this->getOrSet('email', $email);
    }

    public function password($password = null)
    {
        if (is_string($password)) {
            $password = Hash::make($password);
        }

        return $this->getOrSet('password', $password);
    }

    public function isSecured()
    {
        return true;
    }

    public function status()
    {
        // TODO
    }

    public function roles($roles = null)
    {
        return is_null($roles)
            ? $this->getRoles()
            : $this->setRoles($roles);
    }

    protected function getRoles()
    {
        return $this->roles = $this->roles
            ?? (new Roles($this))->all()->map(function ($row) {
                return Role::find($row->role_id);
            });
    }

    protected function setRoles($roles)
    {
        $this->roles = collect();

        $this->assignRoles($roles);

        return $this;
    }

    protected function saveRoles()
    {
        $roles = $this->roles()->map->id();

        (new Roles($this))->sync($roles);
    }

    public function assignRole($role)
    {
        $roles = collect(array_wrap($role))->map(function ($role) {
            return is_string($role) ? Role::find($role) : $role;
        })->filter();

        $this->roles = $this->roles ?? collect();

        $roles->each(function ($role) {
            $this->roles->put($role->id(), $role);
        });

        return $this;
    }

    public function removeRole($role)
    {
        $roles = collect(array_wrap($role))->map(function ($role) {
            return is_string($role) ? Role::find($role) : $role;
        })->filter();

        $roles->each(function ($role) {
            $this->roles->forget($role->id());
        });

        return $this;
    }

    public function hasRole($role)
    {
        return $this->roles->has(
            is_string($role) ? $role : $role->handle()
        );
    }

    public function groups($groups = null)
    {
        return is_null($groups)
            ? $this->getGroups()
            : $this->setGroups($groups);
    }

    protected function getGroups()
    {
        return $this->groups = $this->groups
            ?? (new UserGroups($this))->all()->map(function ($row) {
                return UserGroup::find($row->group_id);
            });
    }

    protected function setGroups($groups)
    {
        $this->groups = collect();

        $this->addToGroup($groups);

        return $this;
    }

    protected function saveGroups()
    {
        $groups = $this->groups()->map->id();

        (new UserGroups($this))->sync($groups);
    }

    public function addToGroup($group)
    {
        $groups = collect(array_wrap($group))->map(function ($group) {
            return is_string($group) ? UserGroup::find($group) : $group;
        })->filter();

        $this->groups = $this->groups ?? collect();

        $groups->each(function ($group) {
            $this->groups->put($group->id(), $group);
        });

        return $this;
    }

    public function removeFromGroup($group)
    {
        $groups = collect(array_wrap($group))->map(function ($group) {
            return is_string($group) ? group::find($group) : $group;
        })->filter();

        $groups->each(function ($group) {
            $this->groups->forget($group->id());
        });

        return $this;
    }

    public function isInGroup($group)
    {
        return $this->groups->has(
            is_string($group) ? $group : $group->handle()
        );
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
        if ((bool) $this->model()->super) {
            return true;
        }

        return $this->hasPermission('super');
    }

    public function makeSuper()
    {
        $this->model()->super = true;

        return $this;
    }

    public function save()
    {
        $this->saveRoles();
        $this->saveGroups();

        $this->model()->save();

        // event(new UserSaved($this, [])); // TODO

        return $this;
    }

    public function delete()
    {
        $this->model()->delete();

        event(new UserDeleted($this->id(), []));
    }

    public function lastModified()
    {
        return $this->model()->updated_at;
    }

    protected function getOrSet($key, $value = null)
    {
        if (is_null($value)) {
            return $this->get($key);
        }

        return $this->set($key, $value);
    }

    public function get($key, $default = null)
    {
        $value = $this->model()->$key;

        return is_null($value) ? $default : $value;
    }

    public function set($key, $value)
    {
        $columns = \Schema::getColumnListing($this->model()->getTable());

        if (array_has(array_flip($columns), $key)) {
            $this->model()->$key = $value;
        }

        return $this;
    }

    public function getRememberToken()
    {
        return $this->model()->getRememberToken();
    }

    public function setRememberToken($value)
    {
        return $this->model()->setRememberToken($value);
    }

    public function getRememberTokenName()
    {
        return $this->model()->getRememberTokenName();
    }
}