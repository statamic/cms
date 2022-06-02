<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Role as RoleFacade;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup;
use Statamic\Facades\YAML;
use Statamic\Stache\Indexes\Users\Group;
use Statamic\Stache\Indexes\Users\Role;

class UsersStore extends BasicStore
{
    protected function storeIndexes()
    {
        return UserGroup::all()->mapWithKeys(function ($group) {
            return ['groups/'.$group->handle() => Group::class];
        })
        ->merge(RoleFacade::all()->mapWithKeys(function ($role) {
            return ['roles/'.$role->handle() => Role::class];
        }))
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

        if (! $id = array_pull($data, 'id')) {
            $idGenerated = true;
            $id = app('stache')->generateId();
        }

        $user = User::make()
            ->id($id)
            ->initialPath($path)
            ->email(pathinfo($path, PATHINFO_FILENAME))
            ->preferences(array_pull($data, 'preferences', []))
            ->data($data);

        if (array_get($data, 'password') || isset($idGenerated)) {
            $user->save();
        }

        // $this->queueGroups($user);

        return $user;
    }
}
