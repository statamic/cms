<?php

namespace Statamic\Auth\File;

use Statamic\Auth\PermissionCache;
use Statamic\Auth\Role as BaseRole;
use Statamic\Events\RoleDeleted;
use Statamic\Events\RoleSaved;
use Statamic\Facades;
use Statamic\Preferences\HasPreferencesInProperty;
use Statamic\Support\Arr;

class Role extends BaseRole
{
    use HasPreferencesInProperty;

    protected $title;
    protected $handle;
    protected $originalHandle;
    protected $permissions;

    public function __construct()
    {
        $this->permissions = collect();
    }

    public function title(string $title = null)
    {
        if (func_num_args() === 0) {
            return $this->title;
        }

        $this->title = $title;

        return $this;
    }

    public function id(): string
    {
        return $this->handle();
    }

    public function handle(string $handle = null)
    {
        if (func_num_args() === 0) {
            return $this->handle;
        }

        if (! $this->originalHandle) {
            $this->originalHandle = $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function originalHandle()
    {
        return $this->originalHandle;
    }

    public function permissions($permissions = null)
    {
        if (is_null($permissions)) {
            return $this->permissions;
        }

        $this->permissions = collect($permissions);

        return $this;
    }

    public function addPermission($permission)
    {
        $this->permissions = $this->permissions
            ->merge(Arr::wrap($permission))
            ->unique()
            ->values();

        app(PermissionCache::class)->clear();

        return $this;
    }

    public function removePermission($permission)
    {
        $this->permissions = $this->permissions
            ->diff(Arr::wrap($permission))
            ->values();

        return $this;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions->contains($permission);
    }

    public function isSuper(): bool
    {
        return $this->hasPermission('super');
    }

    public function save()
    {
        // TODO: Move this logic into \Statamic\Auth\Role.php to be consistent with \Statamic\Auth\UserGroup?

        Facades\Role::save($this);

        RoleSaved::dispatch($this);

        return $this;
    }

    public function delete()
    {
        // TODO: Move this logic into \Statamic\Auth\Role.php to be consistent with \Statamic\Auth\UserGroup?

        Facades\Role::delete($this);

        RoleDeleted::dispatch($this);
    }
}
