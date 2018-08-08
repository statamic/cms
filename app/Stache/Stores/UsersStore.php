<?php

namespace Statamic\Stache\Stores;

use Statamic\API\User;
use Statamic\API\YAML;

class UsersStore extends BasicStore
{
    public function key()
    {
        return 'users';
    }

    public function createItemFromFile($path, $contents)
    {
        $data = YAML::parse($contents);

        $user = User::create()
            ->username(pathinfo($path, PATHINFO_FILENAME))
            ->with($data)
            ->get();

        // @TODO: TDD
        // if ($rawPassword = array_get($data, 'password')) {
        //     $user->securePassword(true, true);
        // }

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
}
