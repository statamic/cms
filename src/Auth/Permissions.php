<?php

namespace Statamic\Auth;

use Illuminate\Support\Facades\Gate;

class Permissions
{
    protected $permissions = [];
    protected $pendingGroup = null;

    public function make(string $value)
    {
        $permission = (new Permission)->value($value);

        if ($this->pendingGroup) {
            $permission->inGroup($this->pendingGroup);
        }

        return $permission;
    }

    public function register($permission, $callback = null)
    {
        if (! $permission instanceof Permission) {
            $permission = self::make($permission);

            if ($callback) {
                $callback($permission);
            }
        }

        $this->permissions[] = $permission;

        return $permission;
    }

    public function all()
    {
        return collect($this->permissions)->flatMap(function ($permission) {
            return $this->mergePermissions($permission);
        })->keyBy->value();
    }

    protected function mergePermissions($permission)
    {
        return $permission->permissions()
            ->merge($permission->children()->flatMap(function ($perm) {
                return $this->mergePermissions($perm);
            }));
    }

    public function tree()
    {
        return collect($this->permissions)->flatMap(function ($permission) {
            return $permission->permissions();
        })->map(function ($permission) {
            return $permission->toTree();
        });
    }

    protected function toTree($permissions)
    {
        return $permissions
            ->keyBy->value()
            ->map(function($permission) {
                return [
                    'permission' => $permission,
                    'children' => $this->toTree($permission->children())
                ];
            });
    }

    public function group($name, $permissions)
    {
        throw_if($this->pendingGroup, new \Exception('Cannot double nest permission groups'));

        $this->pendingGroup = $name;

        $permissions();

        $this->pendingGroup = null;
    }
}
