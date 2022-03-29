<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Auth\File\UserGroup as FileUserGroup;
use Statamic\Facades\User;

class UserGroup extends FileUserGroup
{
    protected $model;

    public static function fromModel(UserGroupModel $model)
    {
        return (new static)
            ->title($model->title)
            ->handle($model->handle)
            ->roles($model->roles)
            ->model($model);
    }

    public function toModel()
    {
        return UserGroupModel::findOrNew($this->model?->id)->fill([
            'title' => $this->title,
            'handle' => $this->handle,
            'roles' => $this->roles->keys(),
        ]);
    }

    public function model($model = null)
    {
        if (func_num_args() === 0) {
            return $this->model;
        }

        $this->model = $model;

        $this->id($model->id);

        return $this;
    }

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
