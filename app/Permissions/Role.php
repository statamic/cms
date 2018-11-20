<?php

namespace Statamic\Permissions;

use Statamic\API\Arr;
use Illuminate\Support\Collection;
use Statamic\Contracts\Permissions\Role as RoleContract;

class Role implements RoleContract
{
    protected $title;
    protected $handle;
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

    public function handle(string $handle = null)
    {
        if (is_null($handle)) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
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

    }

    public function delete()
    {

    }
}
