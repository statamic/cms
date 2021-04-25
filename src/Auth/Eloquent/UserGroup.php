<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Auth\File\UserGroup as FileUserGroup;
use Statamic\Facades\User;

class UserGroup extends FileUserGroup
{
    public function queryUsers()
    {
        return User::query()->whereIn('id', $this->getUserIds());
    }

    protected function getUserIds()
    {
        return \DB::connection(config('statamic.users.database'))
            ->table(config('statamic.users.tables.group_user', 'group_user'))
            ->where('group_id', $this->id())
            ->pluck('user_id');
    }
}
