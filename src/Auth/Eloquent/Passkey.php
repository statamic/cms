<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Statamic\Auth\File\Passkey as BasePasskey;
use Statamic\Support\Arr;

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
            $this->data($model->data, ['id']);
        }

        return $this;
    }

    public function save()
    {
        $model = $this->model() ?? app(config('statamic.webauthn.model'))::findOrNew($this->id());
        $model->id = $this->id();
        $model->user_id = $this->user()?->getKey();
        $model->data = Arr::except($this->fileData(), ['id']);
        $model->save();

        $this->model($model);
    }

    public function delete()
    {
        $this->model()?->delete();
    }
}
