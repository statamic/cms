<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Contracts\Auth\UserRepository;
use Statamic\Auth\File\UserGroup as FileUserGroup;

class UserGroup extends FileUserGroup
{
    public function users($users = null)
    {
        return $this->queryUsers()
            ->whereIn('id', $this->getUserIds())
            ->get();
    }

    protected function getUserIds()
    {
        return \DB::table('group_user')
            ->where('group_id', $this->id())
            ->pluck('user_id');
    }
}
