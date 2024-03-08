<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Statamic\Auth\File\Passkey as BasePasskey;

class Passkey extends BasePasskey
{
    protected $model;

    public function model(?Model $model = null)
    {
        if (is_null($model)) {
            return $this->model;
        }

        $this->model = $model;

        if ($model->id) {
            $this->id($model->id);
            $this->user($model->user_id);
            $this->data($model->data);
        }

        return $this;
    }
}
