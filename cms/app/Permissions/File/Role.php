<?php

namespace Statamic\Permissions\File;

use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\YAML;
use Illuminate\Support\Collection;
use Statamic\Contracts\Permissions\Role as RoleContract;

class Role implements RoleContract
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
     * @var Collection
     */
    protected $permissions;

    /**
     * Create a new Role
     */
    public function __construct()
    {
        $this->permissions = collect();
    }

    /**
     * @param string|null $uuid
     * @return string
     */
    public function uuid($uuid = null)
    {
        if ($uuid) {
            $this->uuid = $uuid;
        }

        return $this->uuid;
    }

    /**
     * @param string|null $title
     * @return string
     */
    public function title($title = null)
    {
        if ($title) {
            $this->title = $title;
        }

        return $this->title;
    }

    public function slug($slug = null)
    {
        if ($slug) {
            $this->slug = $slug;
        }

        return $this->slug ?: Str::slug($this->title(), '_');
    }

    /**
     * @param array|null $permissions
     * @return \Illuminate\Support\Collection
     */
    public function permissions($permissions = null)
    {
        if (is_null($permissions)) {
            return $this->permissions;
        }

        $this->permissions = collect($permissions);
    }

    /**
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if ($this->isSuper()) {
            return true;
        }

        // Search for wildcards
        if (Str::contains($permission, '*')) {
            $found = $this->permissions->search(function ($item) use ($permission) {
                $pattern = '/^' . preg_quote($permission) . '$/';
                $pattern = Str::replace($pattern, '\*', '(?:.*)');

                return preg_match($pattern, $item) === 1;
            });

            if ($found !== false) {
                return true;
            }
        }

        return $this->permissions->search($permission) !== false;
    }

    /**
     * @param string $permission
     * @return mixed
     */
    public function addPermission($permission)
    {
        $this->permissions->push($permission)->unique();
    }

    /**
     * @param string $permission
     * @return mixed
     */
    public function removePermission($permission)
    {
        $key = $this->permissions->search($permission);

        $this->permissions->pull($key);
    }

    /**
     * @return bool
     */
    public function isSuper()
    {
        return $this->permissions->search('super') !== false;
    }

    public function save()
    {
        $path = settings_path('users/roles.yaml');

        $roles = YAML::parse(File::get($path));

        $roles[$this->uuid()] = $this->toArray();

        File::put($path, YAML::dump($roles));
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        $path = settings_path('users/roles.yaml');

        $roles = YAML::parse(File::get($path));

        unset($roles[$this->uuid()]);

        File::put($path, YAML::dump($roles));
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
            'permissions' => $this->permissions->all()
        ];
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
     * The URL to edit it in the CP
     *
     * @return mixed
     */
    public function editUrl()
    {
        return cp_route('user.role', $this->uuid());
    }
}
