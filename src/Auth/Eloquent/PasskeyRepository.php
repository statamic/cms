<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Statamic\Auth\PasskeyCollection;
use Statamic\Auth\File\PasskeyRepository as BaseRepository;
use Statamic\Contracts\Auth\Passkey;
use Statamic\Facades\Blink;

class PasskeyRepository extends BaseRepository
{
    public function __construct()
    {
        $this->config = config('statamic.webauthn');
    }

    public function all(): PasskeyCollection
    {
        $passkeys = $this->model('all')->keyBy('id')->map(function ($model) {
            return $this->make()->model($model);
        });

        return PasskeyCollection::make($passkeys);
    }

    public function find($id): ?Passkey
    {
        return Blink::once("eloquent-passkey-find-{$id}", function () use ($id) {
            if ($model = $this->model('find', $id)) {
                return $this->make()->model($model);
            }

            return null;
        });
    }

    public function model($method, ...$args)
    {
        $model = $this->config['model'];

        return call_user_func_array([$model, $method], $args);
    }

    public function query()
    {
        return new PasskeyQueryBuilder($this->model('query'));
    }

    public function save(Passkey $passkey)
    {
        $model = $passkey->model();
        $model->id = $passkey->id();
        $model->user_id = $passkey->user()?->id;
        $model->data = $passkey->data();
        $model->save();

        Blink::forget("eloquent-passkey-find-{$passkey->id()}");
    }

    public function delete(Passkey $passkey)
    {
        $passkey->model()->delete();

        Blink::forget("eloquent-passkey-find-{$passkey->id()}");
    }

    public static function bindings(): array
    {
        return [
            Passkey::class => \Statamic\Auth\Eloquent\Passkey::class,
            PasskeyQueryBuilder::class => \Statamic\Auth\Eloquent\PasskeyQueryBuilder::class,
        ];
    }

    public function make(): Passkey
    {
        return app(Passkey::class)->model(new $this->config['model']);
    }
}
