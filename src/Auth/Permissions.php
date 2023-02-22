<?php

namespace Statamic\Auth;

use Facades\Statamic\Auth\CorePermissions;

class Permissions
{
    protected $extensions = [];
    protected $permissions = [];
    protected $groups = [];
    protected $pendingGroup = null;

    public function boot()
    {
        $early = $this->permissions;
        $this->permissions = [];

        CorePermissions::boot();

        foreach ($this->extensions as $callback) {
            $callback($this);
        }

        $this->permissions = array_merge($this->permissions, $early);
    }

    public function extend($callback)
    {
        $this->extensions[] = $callback;
    }

    public function make(string $value)
    {
        $permission = (new Permission)->value($value);

        if ($this->pendingGroup) {
            $permission->group($this->pendingGroup);
        }

        return $permission;
    }

    public function register($permission, $callback = null)
    {
        if (! $permission instanceof Permission) {
            $permission = self::make($permission);
        }

        if ($callback) {
            $callback($permission);
        }

        $this->permissions[] = $permission;

        return $permission;
    }

    public function all()
    {
        return collect($this->permissions)
            ->flatMap(function ($permission) {
                return collect([$permission])->merge($this->mergeChildPermissions($permission));
            })
            ->keyBy->originalValue();
    }

    private function mergeChildPermissions($permission)
    {
        $permissions = $permission->children();

        foreach ($permissions as $p) {
            $permissions = $permissions->merge($this->mergeChildPermissions($p));
        }

        return $permissions;
    }

    public function get($key)
    {
        return $this->all()->get($key);
    }

    public function tree()
    {
        $tree = collect($this->permissions)
            ->flatMap(function ($permission) {
                return $permission->permissions()->flatMap->toTree();
            })
            ->groupBy(function ($permission) {
                return $permission['group'] ?? 'misc';
            });

        // Place ungrouped permissions at the end.
        if ($tree->has('misc')) {
            $tree->put('misc', $tree->pull('misc'));
        }

        $tree = $tree->map(function ($permissions, $group) {
            return [
                'handle' => $group,
                'label' => $this->groups[$group] ?? __('Miscellaneous'),
                'permissions' => $permissions->all(),
            ];
        });

        return $tree->values();
    }

    public function group($name, $label, $permissions = null)
    {
        throw_if($this->pendingGroup, new \Exception('Cannot double nest permission groups'));

        if (func_num_args() === 3) {
            $this->groups[$name] = $label;
        }

        if (func_num_args() === 2) {
            $permissions = $label;
        }

        $this->pendingGroup = $name;

        $permissions();

        $this->pendingGroup = null;
    }
}
