<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Auth\File\Role as FileRole;

class Role extends FileRole
{
    protected $model;

    public static function fromModel(RoleModel $model)
    {
        return (new static)
            ->title($model->title)
            ->handle($model->handle)
            ->permissions($model->permissions)
            ->preferences($model->preferences)
            ->model($model);
    }

    public function toModel()
    {
        return RoleModel::findOrNew($this->model?->id)->fill([
            'title' => $this->title,
            'handle' => $this->handle,
            'permissions' => $this->permissions,
            'preferences' => $this->preferences,
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
}
