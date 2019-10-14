<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\File;
use Statamic\Facades\User;
use Statamic\Facades\YAML;
use Statamic\Facades\UserGroup;
use Statamic\Stache\Indexes\Users\Group;
use Symfony\Component\Finder\SplFileInfo;

class UsersStore extends BasicStore
{
    protected function storeIndexes()
    {
        return UserGroup::all()->mapWithKeys(function ($group) {
            return ['groups/'.$group->handle() => Group::class];
        })
        ->push('email')
        ->all();
    }

    protected $groups = [];

    public function key()
    {
        return 'users';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::file($path)->parse($contents);

        $user = User::make()
            ->id(array_pull($data, 'id'))
            ->initialPath($path)
            ->email(pathinfo($path, PATHINFO_FILENAME))
            ->preferences(array_pull($data, 'preferences', []))
            ->data($data);

        if (array_get($data, 'password')) {
            $user->save();
        }

        // $this->queueGroups($user);

        return $user;
    }

    public function filter(SplFileInfo $file)
    {
        return $file->getExtension() === 'yaml';
    }

    // protected function queueGroups($user)
    // {
    //     if (! $groups = $user->get('groups')) {
    //         return;
    //     }

    //     foreach ($groups as $group) {
    //         $this->groups[$group][] = $user;
    //     }
    // }

    // public function loadingComplete()
    // {
    //     foreach ($this->groups as $group => $users) {
    //         if ($group = UserGroup::find($group)) {
    //             $group->users($users)->resetOriginalUsers();
    //         }
    //     }
    // }

    // public function delete($user)
    // {
    //     File::delete($user->path());
    // }
}
