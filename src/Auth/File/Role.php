<?php

namespace Statamic\Auth\File;

use Statamic\Facades;
use Statamic\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Auth\PermissionCache;
use Statamic\Auth\Role as BaseRole;
use Statamic\Contracts\Auth\RoleRepository;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Preferences\HasPreferencesInProperty;

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
        if (is_null($title)) {
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
        Facades\Role::save($this);

        return $this;
    }

    public function delete()
    {
        Facades\Role::delete($this);
    }
}
