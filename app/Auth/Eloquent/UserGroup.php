<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Contracts\Auth\UserRepository;
use Statamic\Auth\File\UserGroup as FileUserGroup;

class UserGroup extends FileUserGroup
{
    public function users($users = null)
    {
        $userIds = \DB::table('group_user')
            ->where('group_id', $this->id())
            ->pluck('user_id');

        return $this
            ->model('whereIn', 'id', $userIds->all())
            ->get()
            ->keyBy('id')
            ->map(function ($model) {
                return User::fromModel($model);
            });
    }

    protected function model($method, ...$args)
    {
        return app(UserRepository::class)->model($method, ...$args);
    }
}