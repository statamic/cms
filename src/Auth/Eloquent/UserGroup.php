<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Auth\File\UserGroup as FileUserGroup;
use Statamic\Facades\User;

class UserGroup extends FileUserGroup
{
    public function users($users = null)
    {
        return User::query()
            ->whereIn('id', $this->getUserIds())
            ->get();
    }

    protected function getUserIds()
    {
        return \DB::table('group_user')
            ->where('group_id', $this->id())
            ->pluck('user_id');
    }

    public function queryUsers()
    {
        return User::query()
            ->whereIn('id', $this->getUserIds() ?: [0]);
    }
}
