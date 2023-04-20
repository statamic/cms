<?php

namespace Statamic\Auth\File;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Statamic\Auth\PermissionCache;
use Statamic\Auth\User as BaseUser;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Data\ContainsData;
use Statamic\Data\Data;
use Statamic\Data\ExistsAsFile;
use Statamic\Facades;
use Statamic\Facades\File;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;
use Statamic\Preferences\HasPreferencesInProperty;
use Statamic\Support\Traits\FluentlyGetsAndSets;

/**
 * A user.
 */
class User extends BaseUser
{
    use ExistsAsFile, FluentlyGetsAndSets, HasPreferencesInProperty, ContainsData {
        data as traitData;
    }

    protected $id;
    protected $email;
    protected $password;
    protected $permissions;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

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
        return $this->fluentlyGetOrSet('id')->args(func_get_args());
    }

    /**
     * Get or set a user's email.
     *
     * @param  string|null  $email
     * @return mixed
     */
    public function email($email = null)
    {
        return $this->fluentlyGetOrSet('email')->args(func_get_args());
    }

    /**
     * Get or set a user's password.
     *
     * @param  string|null  $password
     * @return string
     */
    public function password($password = null)
    {
        return $this
            ->fluentlyGetOrSet('password')
            ->setter(function ($password) {
                return Hash::make($password);
            })
            ->args(func_get_args());
    }

    public function passwordHash($hash = null)
    {
        return $this->fluentlyGetOrSet('password')->args(func_get_args());
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('users')->directory(), '/'),
            $this->email(),
        ]);
    }

    /**
     * The timestamp of the last modification date and time.
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
        return $this->getMeta('remember_token');
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($token)
    {
        $this->setMeta('remember_token', $token);
    }

    /**
     * Get the column name for the "remember me" token.
     * It's a required Laravel thing.
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
                return Facades\Role::find($role);
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
            return Facades\UserGroup::find($group);
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
        $cache = app(PermissionCache::class);

        if ($cached = $cache->get($this->id)) {
            return $cached;
        }

        $permissions = $this
            ->groups()
            ->flatMap->roles()
            ->merge($this->roles())
            ->flatMap->permissions();

        if ($this->get('super', false)) {
            $permissions[] = 'super';
        }

        $permissions = $permissions->unique()->values();

        $cache->put($this->id, $permissions);

        return $permissions;
    }

    public function hasPermission($permission)
    {
        return $this->permissions()->contains($permission);
    }

    public function makeSuper()
    {
        $this->set('super', true);

        return $this;
    }

    public function lastLogin()
    {
        $last_login = $this->getMeta('last_login');

        return $last_login ? Carbon::createFromTimestamp($last_login) : $last_login;
    }

    public function setLastLogin($carbon)
    {
        $this->setMeta('last_login', $carbon->timestamp);
    }

    /**
     * Get a value from the user's meta YAML file.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getMeta($key, $default = null)
    {
        $yaml = YAML::file($this->metaPath())->parse();

        return array_get($yaml, $key, $default);
    }

    /**
     * Write to the user's meta YAML file.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function setMeta($key, $value)
    {
        $yaml = YAML::file($this->metaPath())->parse();

        $yaml[$key] = $value;

        File::put($this->metaPath(), YAML::dump($yaml));
    }

    /**
     * Path to the user's meta YAML file.
     *
     * @return string
     */
    protected function metaPath()
    {
        return storage_path("statamic/users/{$this->id}.yaml");
    }

    public function fileData()
    {
        return $this->data()->merge([
            'id' => (string) $this->id(),
            'password_hash' => $this->password(),
            'preferences' => $this->preferences(),
        ])->all();
    }

    public function fresh()
    {
        return Facades\User::find($this->id);
    }
}
