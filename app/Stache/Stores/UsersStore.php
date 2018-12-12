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
            $user = User::make()
                ->email($item['attributes']['email'])
                ->data($item['data'][default_locale()])
                ->syncOriginal();

            $this->queueGroups($user);

            return $user;
        });
    }

    public function createItemFromFile($path, $contents)
    {
        $data = YAML::parse($contents);

        $user = User::make()
            ->email(pathinfo($path, PATHINFO_FILENAME))
            ->data($data);

        // TODO: TDD
        if ($rawPassword = array_get($data, 'password')) {
            $user->securePassword(true, true);
        }

        $user->syncOriginal();

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
        $data = $user->data();
        $content = array_pull($data, 'content');
        $data = $this->removeNullValues($data);
        $contents = YAML::dump($data, $content);

        $path = sprintf('%s/%s.yaml', $this->directory, $user->email());

        $this->files->put($path, $contents);

        // TODO: Logic for deleting the old file if the email had changed.
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
