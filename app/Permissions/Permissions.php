<?php

namespace Statamic\Permissions;

use Illuminate\Support\Facades\Gate;

class Permissions
{
    protected static $permissions = [];

    public function make(string $value)
    {
        return (new Permission)->value($value);
    }

    public function register($permission, $callback = null)
    {
        if (! $permission instanceof Permission) {
            $permission = self::make($permission);

            if ($callback) {
                $callback($permission);
            }
        }

        static::$permissions[] = $permission;

        if (! $permission->placeholder()) {
            $permission->permissions(true)->each(function ($permission) {
                Gate::define($permission->value(), function ($user) use ($permission) {
                    return $user->hasPermission($permission->value());
                });
            });
        }

        return $permission;
    }

    public function all()
    {
        return collect(static::$permissions)->flatMap(function ($permission) {
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
        return collect(static::$permissions)->flatMap(function ($permission) {
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
}
