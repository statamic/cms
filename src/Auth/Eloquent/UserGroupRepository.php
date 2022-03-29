<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Support\Collection;
use Statamic\Auth\File\UserGroupRepository as BaseRepository;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;

class UserGroupRepository extends BaseRepository
{
    public function make()
    {
        return new UserGroup;
    }

    public function save($userGroup)
    {
        $model = $userGroup->toModel();

        $model->save();

        $userGroup->model($model->fresh());
    }

    public function delete($userGroup)
    {
        $userGroup->model()->delete();
    }

    public static function bindings(): array
    {
        return [
            UserGroupContract::class => UserGroupModel::class,
        ];
    }

    public function all(): Collection
    {
        return UserGroupModel::all()->map(function ($model) {
            return (new UserGroup)::fromModel($model);
        });
    }

    public function find($id): ?UserGroupContract
    {
        $model = UserGroupModel::whereHandle($id)->first();

        return $model ? (new UserGroup)->fromModel($model) : null;
    }
}
