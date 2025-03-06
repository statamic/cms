<?php

namespace Statamic\Auth\File;

use Carbon\Carbon;
use Statamic\Contracts\Auth\Passkey as PasskeyContract;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Data\ContainsData;
use Statamic\Facades;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Webauthn\PublicKeyCredentialSource;

class Passkey implements PasskeyContract
{
    use ContainsData, FluentlyGetsAndSets;

    protected $id;
    protected $user;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function id($id = null)
    {
        return $this->fluentlyGetOrSet('id')->args(func_get_args());
    }

    public function user($user = null)
    {
        return $this
            ->fluentlyGetOrSet('user')
            ->setter(function ($user) {
                return $user instanceof UserContract ? $user->id() : $user;
            })
            ->getter(function ($id) {
                return Facades\User::find($id);
            })
            ->args(func_get_args());
    }

    public function delete()
    {
        if (! $user = $this->user()) {
            return;
        }

        $user->passkeys($user->passkeys()->reject(fn ($key) => $key->id() == $this->id()));

        $user->save();
    }

    public function save()
    {
        if (! $user = $this->user()) {
            return;
        }

        $passkeys = $user->passkeys()->reject(fn ($key) => $key->id() == $this->id());

        $user->passkeys($passkeys->push($this));

        $user->save();
    }

    public function fileData()
    {
        return $this->data()->merge([
            'id' => (string) $this->id(),
        ])->all();
    }

    public function lastLogin()
    {
        return ($login = $this->get('last_login')) ? Carbon::createFromTimestamp($login) : null;
    }

    public function toPublicKeyCredentialSource()
    {
        $data = $this->data()->all();

        $data['trustPath'] = [
            'type' => \Webauthn\TrustPath\EmptyTrustPath::class,
        ];

        return PublicKeyCredentialSource::createFromArray($data);
    }
}
