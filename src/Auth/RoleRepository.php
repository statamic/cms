<?php

namespace Statamic\Auth;

use Illuminate\Support\Collection;
use Statamic\Contracts\Auth\Role;
use Statamic\Contracts\Auth\RoleRepository as RepositoryContract;
use Statamic\Facades;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;

abstract class RoleRepository implements RepositoryContract
{
    protected $path;
    protected $roles;

    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    public function all(): Collection
    {
        if ($this->roles) {
            return $this->roles;
        }

        return $this->roles = $this->raw()->map(function ($role, $handle) {
            return Facades\Role::make()
                ->handle($handle)
                ->title(array_get($role, 'title'))
                ->addPermission(array_get($role, 'permissions', []))
                ->preferences(array_get($role, 'preferences', []));
        });
    }

    public function find(string $id): ?Role
    {
        return $this->all()->get($id);
    }

    public function exists(string $id): bool
    {
        return $this->find($id) !== null;
    }

    public function save(Role $role)
    {
        $roles = $this->raw();

        $roles->put($role->handle(), Arr::removeNullValues([
            'title' => $role->title(),
            'permissions' => $role->permissions()->all(),
            'preferences' => $role->preferences(),
        ]));

        if ($role->handle() !== $role->originalHandle()) {
            $roles->forget($role->originalHandle());
        }

        $this->write($roles);

        $this->roles = null;
    }

    public function delete(Role $role)
    {
        $roles = $this->raw();

        $roles->forget($role->handle());

        $this->write($roles);

        $this->roles = null;
    }

    protected function raw()
    {
        if (! File::exists($this->path)) {
            return collect();
        }

        return collect(YAML::parse(File::get($this->path)));
    }

    protected function write($roles)
    {
        File::put($this->path, YAML::dump($roles->all()));
    }
}
