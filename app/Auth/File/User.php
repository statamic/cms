<?php

namespace Statamic\Auth\File;

use Statamic\API;
use Carbon\Carbon;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Hash;
use Statamic\API\YAML;
use Statamic\Data\Data;
use Statamic\API\Config;
use Statamic\API\Blueprint;
use Statamic\Auth\User as BaseUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Contracts\Auth\Permissible as PermissibleContract;

/**
 * A user
 */
class User extends BaseUser
{
    /**
     * Array of OAuth IDs stored in the YAML file
     *
     * @var array
     */
    private static $oauth_ids;

    /**
     * Get or set a user's email
     *
     * @param string|null $email
     * @return mixed
     */
    public function email($email = null)
    {
        if (is_null($email)) {
            return $this->attributes['email'];
        }

        $this->attributes['email'] = $email;

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
        if (is_null($password)) {
            $this->ensureSecured();

            return $this->get('password_hash');
        }

        $this->set('password', $password);
        $this->remove('password_hash');

        $this->securePassword(false);
    }

    /**
     * Get or set the path to the file
     *
     * @param string|null $path
     * @return string
     * @throws \Exception
     */
    public function path($path = null)
    {
        if ($path) {
            throw new \Exception('You cant set the path of a file.');
        }

        if (! $path = $this->email()) {
            throw new \Exception('Cannot get the path of a user without an email.');
        }

        return $path . '.yaml';
    }

    /**
     * Get the path to a localized version
     *
     * @param string $locale
     * @return string
     */
    public function localizedPath($locale)
    {
        // TODO:
        dd('todo user@localizedpath');
    }

    /**
     * Whether a file should be written to disk when saving.
     *
     * @return bool
     */
    protected function shouldWriteFile()
    {
        return true;
    }

    /**
     * Ensure's this user's password is secured
     *
     * @param bool $save Whether the save after securing
     * @throws \Exception
     */
    public function ensureSecured($save = true)
    {
        // If they don't have a password set, their status is pending.
        // It's not "secured" but there's also nothing *to* secure.
        if ($this->status() == 'pending') {
            return;
        }

        if (! $this->isSecured()) {
            $this->securePassword($save);
        }
    }

    /**
     * Check if the password is secured
     *
     * @return bool
     */
    public function isSecured()
    {
        return (bool) $this->get('password_hash', false);
    }

    /**
     * Secure the password
     *
     * @param bool $save  Whether to save the user
     */
    public function securePassword($save = true)
    {
        if ($this->isSecured()) {
            return;
        }

        if ($password = $this->get('password')) {
            $password = Hash::make($password);

            $this->set('password_hash', $password);
            $this->remove('password');
        }

        if ($save) {
            $this->save();
        }
    }

    /**
     * Get the user's status
     *
     * @return string
     */
    public function status()
    {
        if (! $this->get('password') && ! $this->get('password_hash')) {
            return 'pending';
        }

        return 'active';
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

    /**
     * Get the path to the remember me tokens file
     *
     * @return string
     */
    private function rememberPath()
    {
        return cache_path('remember_me.yaml');
    }

    /**
     * Get the user's OAuth ID for the requested provider
     *
     * @return string
     */
    public function getOAuthId($provider)
    {
        if (! self::$oauth_ids) {
            self::$oauth_ids = YAML::parse(File::get($this->oAuthIdsPath(), ''));
        }

        return array_get(self::$oauth_ids, $provider.'.'.$this->id());
    }

    /**
     * Set a user's oauth ID
     *
     * @param string $provider
     * @param string $id
     * @return void
     */
    public function setOAuthId($provider, $id)
    {
        $yaml = YAML::parse(File::get($this->oAuthIdsPath(), ''));

        $yaml[$provider][$this->id()] = $id;

        File::put($this->oAuthIdsPath(), YAML::dump($yaml));
    }

    /**
     * Get the path to the oauth IDs file
     *
     * @return string
     */
    private function oAuthIdsPath()
    {
        return cache_path('oauth_ids.yaml');
    }

    /**
     * Get the path before the object was modified.
     *
     * @return string
     * @throws \Exception
     */
    public function originalPath()
    {
        if (! $this->original) {
            return null;
        }

        if (! $path = $this->original['attributes']['email']) {
            throw new \Exception('Cannot get the path of a user without an email.');
        }

        return $path . '.yaml';
    }

    /**
     * Get the path to a localized version before the object was modified.
     *
     * @param string $locale
     * @return string
     */
    public function originalLocalizedPath($locale)
    {
        // TODO:
        dd('todo: extend data@localizedPath');
    }

    /**
     * Delete the data
     *
     * @return mixed
     */
    public function delete()
    {
        File::disk('users')->delete($this->path());

        // Whoever wants to know about it can do so now.
        $event_class = 'Statamic\Events\Data\UserDeleted';
        event(new $event_class($this->id(), [$this->path()]));
    }

    /**
     * Whether the data can be taxonomized
     *
     * @return bool
     */
    public function isTaxonomizable()
    {
        return true;
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
}
