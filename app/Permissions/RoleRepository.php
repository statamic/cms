<?php

namespace Statamic\Permissions;

use Statamic\API\File;
use Statamic\API\YAML;
use Illuminate\Support\Collection;
use Statamic\Contracts\Permissions\Role;
use Statamic\Contracts\Permissions\RoleRepository as RepositoryContract;

class RoleRepository implements RepositoryContract
{
    protected $path;

    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    public function all(): Collection
    {
        return $this->raw()->map(function ($role, $handle) {
            // TODO: Use a factory
            return app(Role::class)
                ->handle($handle)
                ->title(array_get($role, 'title'))
                ->addPermission(array_get($role, 'permissions', []));
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

        $roles->put($role->handle(), [
            'title' => $role->title(),
            'permissions' => $role->permissions()->all()
        ]);

        if ($original = $role->originalHandle()) {
            $roles->forget($original);
        }

        $this->write($roles);
    }

    public function delete(Role $role)
    {
        $roles = $this->raw();

        $roles->forget($role->handle());

        $this->write($roles);
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
