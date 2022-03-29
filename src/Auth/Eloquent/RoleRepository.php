<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Support\Collection;
use Statamic\Auth\RoleRepository as BaseRepository;
use Statamic\Contracts\Auth\Role as RoleContract;

class RoleRepository extends BaseRepository
{
    public function make(string $handle = null): RoleContract
    {
        return (new Role)->handle($handle);
    }

    public function save($role)
    {
        $model = $role->toModel();
        $model->save();

        $role->model($model->fresh());
    }

    public function delete($role)
    {
        $role->model()->delete();
    }

    public static function bindings(): array
    {
        return [
            RoleContract::class => Role::class,
        ];
    }

    public function all(): Collection
    {
        return RoleModel::all()->map(function ($model) {
            return (new Role)::fromModel($model);
        });
    }

    public function find($handle): ?RoleContract
    {
        $model = RoleModel::whereHandle($handle)->first();

        return $model ? (new Role)->fromModel($model) : null;
    }
}
