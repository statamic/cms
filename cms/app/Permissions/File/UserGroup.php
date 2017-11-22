<?php

namespace Statamic\Permissions\File;

use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\User;
use Statamic\API\YAML;
use Statamic\API\Role as RoleAPI;
use Statamic\Events\Data\UserGroupDeleted;
use Statamic\Contracts\Permissions\UserGroup as UserGroupContract;
use Statamic\Contracts\Permissions\Permissible as PermissibleContract;

class UserGroup implements UserGroupContract
{
    /**
     * @var string
     */
    protected $uuid;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $users;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $roles;

    /**
     * Create a new Role
     */
    public function __construct()
    {
        $this->roles = collect();
        $this->users = collect();
    }

    /**
     * Get or set the ID
     *
     * @param string|null $id
     * @return mixed
     */
    public function id($id = null)
    {
        if ($id) {
            $this->uuid = $id;
        }

        return $this->uuid;
    }

    /**
     * Get or set the UUID
     *
     * @param string|null $uuid
     * @return mixed
     * @deprecated
     */
    public function uuid($uuid = null)
    {
        return $this->id($uuid);
    }

    /**
     * Get or set the title
     *
     * @param string|null $title
     * @return mixed
     */
    public function title($title = null)
    {
        if ($title) {
            $this->title = $title;
        }

        return $this->title ?: $this->id();
    }

    /**
     * Get or set the slug
     *
     * @param string|null $slug
     * @return mixed
     */
    public function slug($slug = null)
    {
        if ($slug) {
            $this->slug = $slug;
        }

        return $this->slug ?: Str::slug($this->title(), '_');
    }

    /**
     * Get or set the users
     *
     * @param array|null $users
     * @return \Statamic\Data\Users\UserCollection
     */
    public function users($users = null)
    {
        if (is_null($users)) {
            return collect_users($this->users->map(function($id) {
                return User::find($id);
            }));
        }

        $this->users = collect($users);
    }

    /**
     * Add a user to the group
     *
     * @param string|\Statamic\Contracts\Permissions\Permissible $user
     * @return mixed
     */
    public function addUser($user)
    {
        if ($user instanceof PermissibleContract) {
            $user = $user->id();
        }

        if ($this->users->search($user) === false) {
            $this->users->push($user);
        }
    }

    /**
     * Remove a user from the group
     *
     * @param string|\Statamic\Contracts\Permissions\Permissible $user
     * @return mixed
     */
    public function removeUser($user)
    {
        if ($user instanceof PermissibleContract) {
            $user = $user->id();
        }

        $key = $this->users->search($user);

        $this->users->pull($key);
    }

    /**
     * Does a given user exist in this group?
     *
     * @param string|\Statamic\Contracts\Permissions\PermissibleContract $user
     * @return mixed
     */
    public function hasUser($user)
    {
        if ($user instanceof PermissibleContract) {
            $user = $user->id();
        }

        return $this->users->search($user) !== false;
    }

    /**
     * Get or set the roles
     *
     * @param array|null $roles
     * @return \Illuminate\Support\Collection
     */
    public function roles($roles = null)
    {
        if (is_null($roles)) {
            return collect($this->roles->map(function($role) {
                return RoleAPI::find($role);
            }));
        }

        $this->roles = collect($roles);
    }

    /**
     * Does this group have a given role?
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->roles->search($role) !== false;
    }

    /**
     * Add a role to this group
     *
     * @param string|Role $role
     * @return mixed
     */
    public function addRole($role)
    {
        $this->roles->push($role);
    }

    /**
     * Remove a role from this group
     *
     * @param $role
     * @return mixed
     */
    public function removeRole($role)
    {
        $key = $this->roles->search($role);

        $this->roles->pull($key);
    }

    /**
     * Does this group have a given permission?
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        foreach ($this->roles() as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does this group have super permissions?
     *
     * @return bool
     */
    public function isSuper()
    {
        return $this->hasPermission('super');
    }

    /**
     * Save this group
     *
     * @return mixed
     */
    public function save()
    {
        $path = settings_path('users/groups.yaml');

        $groups = YAML::parse(File::get($path));

        $groups[$this->id()] = $this->toArray();

        File::put($path, YAML::dump($groups));

        // Whoever wants to know about it can do so now.
        event('usergroup.saved', $this);
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'title' => $this->title(),
            'slug' => $this->slug(),
            'roles' => $this->roles->all(),
            'users' => $this->users->all()
        ];
    }

    /**
     * The URL to edit it in the CP
     *
     * @return mixed
     */
    public function editUrl()
    {
        return cp_route('user.group', $this->id());
    }

    /**
     * Delete this group
     *
     * @return mixed
     */
    public function delete()
    {
        $path = settings_path('users/groups.yaml');

        $groups = YAML::parse(File::get($path));

        unset($groups[$this->id()]);

        File::put($path, YAML::dump($groups));

        // Whoever wants to know about it can do so now.
        event(new UserGroupDeleted($this->id(), []));
    }
}
