<?php

namespace Statamic\Auth\File;

use Statamic\API;
use Carbon\Carbon;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\Data\Data;
use Statamic\API\Stache;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Auth\User as BaseUser;
use Illuminate\Support\Facades\Hash;
use Statamic\Preferences\HasPreferences;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;

/**
 * A user
 */
class User extends BaseUser
{
    use ExistsAsFile, HasPreferences, ContainsData {
        data as traitData;
    }

    protected $id;
    protected $email;
    protected $password;

    public function data($data = null)
    {
        if (func_num_args() === 0) {
            return $this->traitData();
        }

        $this->traitData($data);

        if (array_has($data, 'password')) {
            $this->remove('password')->password($data['password']);
        }

        if (array_has($data, 'password_hash')) {
            $this->remove('password_hash')->passwordHash($data['password_hash']);
        }

        return $this;
    }

    public function id($id = null)
    {
        if (func_num_args() === 0) {
            return $this->id;
        }

        $this->id = $id;

        return $this;
    }

    /**
     * Get or set a user's email
     *
     * @param string|null $email
     * @return mixed
     */
    public function email($email = null)
    {
        if (func_num_args() === 0) {
            return $this->email;
        }

        $this->email = $email;

        return $this;
    }

    /**
     * Get or set a user's password
     *
     * @param string|null $password
     * @return string
     */
    public function password($password = null)
    {
        if (func_num_args() === 0) {
            return $this->password;
        }

        $this->password = Hash::make($password);

        return $this;
    }

    public function passwordHash($hash = null)
    {
        if (func_num_args() === 0) {
            return $this->password;
        }

        $this->password = $hash;

        return $this;
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('users')->directory(), '/'),
            $this->email(),
        ]);
    }

    /**
     * The timestamp of the last modification date.
     *
     * @return \Carbon\Carbon
     */
    public function lastModified()
    {
        // Users with no files have been created programmatically and haven't
        // been saved yet. We'll use the current time in that case.
        $timestamp = File::disk('users')->exists($path = $this->path())
            ? File::disk('users')->lastModified($path)
            : time();

        return Carbon::createFromTimestamp($timestamp);
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        $yaml = YAML::parse(File::get($this->rememberPath(), ''));

        return array_get($yaml, $this->id());
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     * @return void
     */
    public function setRememberToken($token)
    {
        $yaml = YAML::parse(File::get($this->rememberPath(), ''));

        $yaml[$this->id()] = $token;

        File::put($this->rememberPath(), YAML::dump($yaml));
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    public function roles($roles = null)
    {
        return is_null($roles)
            ? $this->getRoles()
            : $this->set('roles', $roles);
    }

    protected function getRoles()
    {
        return collect($this->get('roles', []))
            ->map(function ($role) {
                return API\Role::find($role);
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
        return is_null($groups)
            ? $this->getGroups()
            : $this->setGroups($groups);
    }

    protected function getGroups()
    {
        return collect($this->get('groups', []))->map(function ($group) {
            return API\UserGroup::find($group);
        })->filter()->keyBy->handle();
    }

    protected function setGroups($groups)
    {
        $this->set('groups', []);

        return $this->addToGroup($groups);
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

    public function toCacheableArray()
    {
        return [
            'path' => $this->path(),
            'email' => $this->email,
            'password' => $this->password,
            'data' => $this->data(),
        ];
    }

    protected function fileData()
    {
        return array_merge($this->data(), [
            'id' => (string) $this->id(),
            'password_hash' => $this->password(),
        ]);
    }
}
