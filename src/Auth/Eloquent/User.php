<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Statamic\Auth\User as BaseUser;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Data\ContainsSupplementalData;
use Statamic\Facades\Role;
use Statamic\Facades\UserGroup;
use Statamic\Preferences\HasPreferences;
use Statamic\Support\Arr;

class User extends BaseUser
{
    use ContainsSupplementalData, HasPreferences;

    protected $model;
    protected $roles;
    protected $groups;

    public function model(?Model $model = null)
    {
        if (is_null($model)) {
            return $this->model;
        }

        $this->model = $model;

        return $this;
    }

    /**
     * Get or set all the data for the current locale.
     *
     * @param  array|null  $data
     * @return $this|array
     */
    public function data($data = null)
    {
        if (func_num_args() === 0) {
            $data = array_merge($this->model()->attributesToArray(), [
                'roles' => $this->roles()->map->handle()->values()->all(),
                'groups' => $this->groups()->map->handle()->values()->all(),
            ]);

            return collect(Arr::except($data, ['id', 'email']));
        }

        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    public function id($id = null)
    {
        if (func_num_args() === 0) {
            return $this->model()->getKey();
        }

        return $this->set('id', $id);
    }

    public function email($email = null)
    {
        return $this->getOrSet('email', $email);
    }

    public function password($password = null)
    {
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

    public function roles(): Collection
    {
        return $this->explicitRoles()
            ->merge($this->groups()->flatMap->roles()->keyBy->handle());
    }

    public function explicitRoles($roles = null)
    {
        if (func_num_args() === 1) {
            $this->roles = collect();

            $this->assignRole($roles);

            return $this;
        }

        return $this->roles = $this->roles
            ?? (new Roles($this))->all()->map(function ($row) {
                return Role::find($row->role_id);
            })->filter()->keyBy->handle();
    }

    protected function saveRoles()
    {
        $roles = $this->roles()->map->id();

        (new Roles($this))->sync($roles);
    }

    public function assignRole($role)
    {
        $roles = collect(Arr::wrap($role))->map(function ($role) {
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
        $roles = collect(Arr::wrap($role))->map(function ($role) {
            return is_string($role) ? Role::find($role) : $role;
        })->filter();

        $roles->each(function ($role) {
            $this->roles->forget($role->id());
        });

        return $this;
    }

    public function groups($groups = null)
    {
        return is_null($groups)
            ? $this->getGroups()
            : $this->setGroups($groups);
    }

    public function hasRole($role)
    {
        $role = $role instanceof RoleContract ? $role->handle() : $role;

        return $this->roles()->has($role);
    }

    protected function getGroups()
    {
        return $this->groups = $this->groups
            ?? (new UserGroups($this))->all()->map(function ($row) {
                return UserGroup::find($row->group_id);
            })->keyBy->handle();
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
        $groups = collect(Arr::wrap($group))->map(function ($group) {
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
        $groups = collect(Arr::wrap($group))->map(function ($group) {
            return is_string($group) ? UserGroup::find($group) : $group;
        })->filter();

        $groups->each(function ($group) {
            $this->groups->forget($group->id());
        });

        return $this;
    }

    public function isInGroup($group)
    {
        return $this->groups()->has(
            is_string($group) ? $group : $group->handle()
        );
    }

    public function permissions()
    {
        $permissions = $this->groups()->flatMap->roles()
            ->merge($this->roles())
            ->flatMap->permissions();

        if ($this->get('super', false)) {
            $permissions[] = 'super';
        }

        return $permissions;
    }

    public function hasPermission($permission)
    {
        return $this->permissions()->contains($permission);
    }

    public function makeSuper()
    {
        $this->model()->super = true;

        return $this;
    }

    public function saveToDatabase()
    {
        $this->model()->save();

        $this->saveRoles();

        $this->saveGroups();
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

    public function has($key)
    {
        return $this->get($key) !== null;
    }

    public function get($key, $default = null)
    {
        $value = $this->model()->$key;

        return is_null($value) ? $default : $value;
    }

    public function set($key, $value)
    {
        if ($key === 'password') {
            $value = Hash::make($value);
        }

        if ($value === null) {
            unset($this->model()->$key);

            return $this;
        }

        $this->model()->$key = $value;

        return $this;
    }

    public function remove($key)
    {
        $this->model()->$key = null;

        return $this;
    }

    public function merge($data)
    {
        $this->data($this->data()->merge(collect($data)->filter(fn ($v) => $v !== null)->all()));

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

    public function sendPasswordResetNotification($token)
    {
        if (method_exists($this->model(), 'sendPasswordResetNotification')) {
            return $this->model()->sendPasswordResetNotification($token);
        }

        parent::sendPasswordResetNotification($token);
    }

    public function sendActivateAccountNotification($token)
    {
        if (method_exists($this->model(), 'sendActivateAccountNotification')) {
            return $this->model()->sendActivateAccountNotification($token);
        }

        parent::sendActivateAccountNotification($token);
    }

    public function lastLogin()
    {
        if (! $date = $this->model()->last_login) {
            return null;
        }

        return $date instanceof Carbon ? $date : Carbon::createFromFormat($this->model()->getDateFormat(), $date);
    }

    public function setLastLogin($time)
    {
        $model = $this->model();

        $model->last_login = $model->fromDateTime($time);

        $timestamps = $model->timestamps;

        $model->timestamps = false;

        $model->save();

        $model->timestamps = $timestamps;
    }

    protected function getPreferences()
    {
        if (! $preferences = $this->model()->preferences) {
            return [];
        }

        return is_string($preferences) ? json_decode($preferences, true) : $preferences;
    }

    public function setPreferences($preferences)
    {
        $this->model()->preferences = $preferences;
    }

    public function mergePreferences($preferences)
    {
        $this->model()->preferences = array_merge($this->getPreferences(), Arr::wrap($preferences));
    }

    public function __set($key, $value)
    {
        if ($key === 'timestamps') {
            return $this->model()->timestamps = $value;
        }

        if ($key === 'super') {
            return $this->model()->super = $value;
        }

        return $this->$key = $value;
    }

    public function getCurrentDirtyStateAttributes(): array
    {
        return array_merge([
            'email' => $this->email(),
        ], $this->model()->attributesToArray());
    }

    public function getLastTwoFactorChallenged(): ?string
    {
        $lastChallenged = $this->get('two_factor_last_challenged');

        if (! $lastChallenged) {
            return null;
        }

        return decrypt($lastChallenged);
    }

    public function setLastTwoFactorChallenged(): self
    {
        $this->set('two_factor_last_challenged', encrypt(Carbon::now()));

        return $this;
    }

    public function clearLastTwoFactorChallenged(): self
    {
        $this->remove('two_factor_last_challenged');

        return $this;
    }
}
