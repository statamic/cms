<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Support\Collection;
use Statamic\Auth\RoleRepository as BaseRepository;
use Statamic\Contracts\Auth\Role as RoleContract;
use Statamic\Facades\Blink;

class RoleRepository extends BaseRepository
{
    public function make(?string $handle = null): RoleContract
    {
        return (new Role)->handle($handle);
    }

    public function save($role)
    {
        if (! $this->isEloquentEnabled()) {
            return parent::save($role);
        }

        $model = $role->toModel();
        $model->save();

        $role->model($model->fresh());

        Blink::forget("eloquent-roles-{$role->handle()}");
    }

    public function delete($role)
    {
        if (! $this->isEloquentEnabled()) {
            return parent::delete($role);
        }

        $role->model()->delete();

        Blink::forget("eloquent-roles-{$role->handle()}");
        Blink::forget('eloquent-roles-all');
    }

    public static function bindings(): array
    {
        return [
            RoleContract::class => Role::class,
        ];
    }

    public function all(): Collection
    {
        if (! $this->isEloquentEnabled()) {
            return parent::all();
        }

        return Blink::once('eloquent-roles-all', function () {
            return RoleModel::all()
                ->map(function ($model) {
                    return Blink::once("eloquent-roles-{$model->handle}", function () use ($model) {
                        return (new Role)->fromModel($model);
                    });
                });
        });
    }

    public function find($handle): ?RoleContract
    {
        if (! $this->isEloquentEnabled()) {
            return parent::find($handle);
        }

        return Blink::once("eloquent-roles-{$handle}", function () use ($handle) {
            $model = RoleModel::where(is_numeric($handle) ? 'id' : 'handle', $handle)->first();

            return $model ? (new Role)->fromModel($model) : null;
        });
    }

    private function isEloquentEnabled()
    {
        return config('statamic.users.tables.roles', false) != false;
    }
}
