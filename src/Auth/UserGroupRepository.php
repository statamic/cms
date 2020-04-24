<?php

namespace Statamic\Auth;

use Statamic\Facades;
use Statamic\Facades\YAML;
use Statamic\Facades\File;
use Statamic\Stache\Stache;
use Illuminate\Support\Collection;
use Statamic\Contracts\Auth\UserGroup;
use Statamic\Contracts\Auth\UserGroupRepository as RepositoryContract;

class UserGroupRepository implements RepositoryContract
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

    public function find($id): ?UserGroup
    {
        return $this->all()->get($id);
    }

    public function username($username)
    {
        // TODO: TDD
        return $this->store->getItems()->first(function ($user) use ($username) {
            return $user->username() === $username;
        });
    }

    public function save(UserGroup $group)
    {
        $groups = $this->raw();

        $groups->put($group->handle(), array_filter([
            'title' => $group->title(),
            'roles' => $group->roles()->map->handle()->values()->all()
        ]));

        if ($group->handle() !== $group->originalHandle()) {
            $groups->forget($group->originalHandle());
        }

        $this->write($groups);

        $this->groups = null;
    }

    public function delete(UserGroup $group)
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
}
