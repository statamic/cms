<?php

namespace Statamic\Stache\Stores;

use Statamic\API\User;
use Statamic\API\YAML;
use Statamic\API\UserGroup;

class UsersStore extends BasicStore
{
    protected $groups = [];

    public function key()
    {
        return 'users';
    }

    public function getItemsFromCache($cache)
    {
        // TODO: TDD
        return $cache->map(function ($item) {
            $user = User::create()
                ->username($item['attributes']['username'])
                ->with($item['data'][default_locale()])
                ->get();

            $this->queueGroups($user);

            return $user;
        });
    }

    public function createItemFromFile($path, $contents)
    {
        $data = YAML::parse($contents);

        $user = User::create()
            ->username(pathinfo($path, PATHINFO_FILENAME))
            ->with($data)
            ->get();

        // TODO: TDD
        if ($rawPassword = array_get($data, 'password')) {
            $user->securePassword(true, true);
        }

        $this->queueGroups($user);

        return $user;
    }

    public function getItemKey($item, $path)
    {
        return $item->id();
    }

    public function filter($file)
    {
        return $file->getExtension() === 'yaml';
    }

    protected function queueGroups($user)
    {
        if (! $groups = $user->get('groups')) {
            return;
        }

        foreach ($groups as $group) {
            $this->groups[$group][] = $user;
        }
    }

    public function loadingComplete()
    {
        foreach ($this->groups as $group => $users) {
            if ($group = UserGroup::find($group)) {
                $group->users($users)->resetOriginalUsers();
            }
        }
    }
}
