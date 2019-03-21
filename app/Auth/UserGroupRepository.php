<?php

namespace Statamic\Auth;

use Statamic\API;
use Statamic\API\YAML;
use Statamic\API\File;
use Statamic\Stache\Stache;
use Illuminate\Support\Collection;
use Statamic\Contracts\Auth\UserGroup;
use Statamic\Contracts\Auth\UserGroupRepository as RepositoryContract;

class UserGroupRepository implements RepositoryContract
{
    protected $groups;
    protected $path;
    protected static $files = [];

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
            $group = API\UserGroup::make()
                ->handle($handle)
                ->title(array_get($data, 'title'));

            foreach ($data['roles'] as $role) {
                if ($role = API\Role::find($role)) {
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

        $groups->put($group->handle(), [
            'title' => $group->title(),
            'roles' => $group->roles()->map->handle()->values()->all()
        ]);

        if ($group->handle() !== $group->originalHandle()) {
            $groups->forget($group->originalHandle());
        }

        $this->updateUsers($group);

        $this->write($groups);
    }

    public function delete(UserGroup $group)
    {
        $groups = $this->raw();

        $groups->forget($group->handle());

        $this->write($groups);
    }

    protected function raw()
    {
        if ($cached = array_get(static::$files, $this->path)) {
            return $cached;
        }

        if (! File::exists($this->path)) {
            return static::$files[$this->path] = collect();
        }

        return static::$files[$this->path] = collect(
            YAML::parse(File::get($this->path))
        );
    }

    protected function write($groups)
    {
        File::put($this->path, YAML::dump($groups->all()));
    }

    protected function updateUsers($group)
    {
        // A bucket of the users that we can pick from, since we'll be diffing with IDs in the next steps.
        $users = $group->users()->merge($group->originalUsers());

        $originals = $group->originalUsers() ?? collect();

        // Update each of the users that have just been added to this group.
        $group->users()->keys()
            ->diff($originals->keys())
            ->each(function ($id) use ($users, $group) {
                $users->get($id)->addToGroup($group)->save();
            });

        // Update each of the users that have just been removed from this group.
        $originals->keys()
            ->diff($group->users()->keys())
            ->each(function ($id) use ($users, $group) {
                $users->get($id)->removeFromGroup($group)->save();
            });

        $group->resetOriginalUsers();
    }
}
