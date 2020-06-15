<?php

namespace Statamic\Auth\File;

use Illuminate\Support\Collection;
use Statamic\Auth\UserGroupRepository as BaseRepository;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Facades;
use Statamic\Facades\File;
use Statamic\Facades\YAML;

class UserGroupRepository extends BaseRepository
{
    protected $groups;
    protected $path;

    public function path($path)
    {
        $this->path = $path;

        return $this;
    }

    public function all(): Collection
    {
        if ($this->groups) {
            return $this->groups;
        }

        return $this->groups = $this->raw()->map(function ($data, $handle) {
            $group = Facades\UserGroup::make()
                ->handle($handle)
                ->title(array_get($data, 'title'));

            foreach ($data['roles'] ?? [] as $role) {
                if ($role = Facades\Role::find($role)) {
                    $group->assignRole($role);
                }
            }

            return $group;
        });
    }

    public function save(UserGroupContract $group)
    {
        $groups = $this->raw();

        $groups->put($group->handle(), array_filter([
            'title' => $group->title(),
            'roles' => $group->roles()->map->handle()->values()->all(),
        ]));

        if ($group->handle() !== $group->originalHandle()) {
            $groups->forget($group->originalHandle());
        }

        $this->write($groups);

        $this->groups = null;
    }

    public function delete(UserGroupContract $group)
    {
        $groups = $this->raw();

        $groups->forget($group->handle());

        $this->write($groups);

        $this->groups = null;
    }

    protected function raw()
    {
        if (! File::exists($this->path)) {
            return collect();
        }

        return collect(YAML::parse(File::get($this->path)));
    }

    protected function write($groups)
    {
        File::put($this->path, YAML::dump($groups->all()));
    }

    public function make()
    {
        return new UserGroup;
    }
}
