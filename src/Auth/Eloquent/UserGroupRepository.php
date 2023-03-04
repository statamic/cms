<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Support\Collection;
use Statamic\Auth\File\UserGroupRepository as BaseRepository;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Facades\Blink;

class UserGroupRepository extends BaseRepository
{
    public function make()
    {
        return new UserGroup;
    }

    public function save($userGroup)
    {
        if (! $this->isEloquentEnabled()) {
            return parent::save($userGroup);
        }

        $model = $userGroup->toModel();

        $model->save();

        $userGroup->model($model->fresh());

        Blink::forget("eloquent-groups-{$userGroup->handle()}");
    }

    public function delete($userGroup)
    {
        if (! $this->isEloquentEnabled()) {
            return parent::delete($userGroup);
        }

        $userGroup->model()->delete();

        Blink::forget("eloquent-groups-{$userGroup->handle()}");
        Blink::forget('eloquent-groups-all');
    }

    public static function bindings(): array
    {
        return [
            UserGroupContract::class => UserGroupModel::class,
        ];
    }

    public function all(): Collection
    {
        if (! $this->isEloquentEnabled()) {
            return parent::all();
        }

        return Blink::once('eloquent-groups-all', function () {
            return UserGroupModel::all()
                ->map(function ($model) {
                    return Blink::once("eloquent-groups-{$model->handle}", function () use ($model) {
                        return (new UserGroup)->fromModel($model);
                    });
                });
        });
    }

    public function find($id): ?UserGroupContract
    {
        if (! $this->isEloquentEnabled()) {
            return parent::find($id);
        }

        return Blink::once("eloquent-groups-{$id}", function () use ($id) {
            $model = UserGroupModel::whereHandle($id)->first();

            return $model ? (new UserGroup)->fromModel($id) : null;
        });
    }

    private function isEloquentEnabled()
    {
        return config('statamic.users.tables.groups', false) != false;
    }
}
