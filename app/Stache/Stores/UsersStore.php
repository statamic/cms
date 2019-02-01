<?php

namespace Statamic\Stache\Stores;

use Statamic\API\File;
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
        return $cache->map(function ($item, $id) {
            $user = User::make()
                ->id($id)
                ->email($item['email'])
                ->initialPath($item['path'])
                ->data($item['data'])
                ->passwordHash($item['password']);

            $this->queueGroups($user);

            return $user;
        });
    }

    public function createItemFromFile($path, $contents)
    {
        $data = YAML::parse($contents);

        $user = User::make()
            ->id(array_pull($data, 'id'))
            ->initialPath($path)
            ->email(pathinfo($path, PATHINFO_FILENAME))
            ->data($data);

        if (array_get($data, 'password')) {
            $user->save();
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

    public function save($user)
    {
        File::put($path = $user->path(), $user->fileContents());

        if (($initial = $user->initialPath()) && $path !== $initial) {
            File::delete($user->initialPath()); // TODO: Test
        }
    }

    /**
     * TODO: Replace this with Arr::removeNullValues from v2.
     * I copied this temporarily to get it working without porting all of the Arr class.
     */
    protected function removeNullValues($data)
    {
        return array_filter($data, function ($item) {
            return is_array($item)
                ? !empty($item)
                : !in_array($item, [null, ''], true);
        });
    }
}
