<?php

namespace Statamic\Auth;

use Illuminate\Support\Collection;
use Statamic\Contracts\Auth\Role;
use Statamic\Contracts\Auth\RoleRepository as RepositoryContract;
use Statamic\Events\RoleBlueprintFound;
use Statamic\Facades\Blueprint;
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
                ->preferences(array_get($role, 'preferences', []))
                ->data($role);
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

        $roles->put($role->handle(), Arr::removeNullValues(array_merge($role->data()->all(), [
            'title' => $role->title(),
            'permissions' => $role->permissions()->all(),
            'preferences' => $role->preferences(),
        ])));

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

    public function blueprint()
    {
        $blueprint = Blueprint::find('user_role') ?? Blueprint::makeFromFields([
            'title' => ['type' => 'text', 'display' => __('Title'), 'listable' => true, 'validate' => ['required'], 'instructions' => __('Usually a singular noun, like Editor or Admin.')],
            'handle' => ['type' => 'slug', 'display' => __('Handle'), 'listable' => true, 'validate' => ['required'], 'instructions' => __('Handles are used to reference this role on the frontend. Cannot be easily changed.')],
            'super' => ['type' => 'toggle', 'display' => __('Super User'), 'listable' => true, 'instructions' => __('Super admins have complete control and access to everything in the control panel. Grant this role wisely.')],
        ])->setHandle('user_role');

        RoleBlueprintFound::dispatch($blueprint);

        return $blueprint;
     }
}
