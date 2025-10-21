<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Statamic\Auth\Passkey as BasePasskey;

class Passkey extends BasePasskey
{
    private ?Model $model = null;

    public function model(): ?Model
    {
        return $this->model;
    }

    public function setModel(Model $model): self
    {
        $this
            ->setId($model->id)
            ->setUser($model->user_id)
            ->setLastLogin($model->last_login)
            ->setCredential($model->credential);

        $this->model = $model;

        return $this;
    }

    public function save(): bool
    {
        $model = $this->model() ?? app(config('statamic.webauthn.model'))::findOrNew($this->id());
        $model->id = $this->id();
        $model->user_id = $this->user()?->getKey();
        $model->last_login = $this->lastLogin();
        $model->credential = $this->credential();

        $result = $model->save();

        $this->setModel($model);

        return $result;
    }

    public function delete(): bool
    {
        return $this->model()->delete();
    }
}
