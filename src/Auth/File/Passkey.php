<?php

namespace Statamic\Auth\File;

use Statamic\Contracts\Auth\Passkey as PasskeyContract;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Facades;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Webauthn\PublicKeyCredentialSource;

class Passkey implements PasskeyContract
{
    use ContainsData, ExistsAsFile, FluentlyGetsAndSets;

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
                return is_string($user) ? $user : $user->id();
            })
            ->getter(function ($id) {
                return Facades\User::find($id);
            })
            ->args(func_get_args());
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('passkeys')->directory(), '/'),
            $this->id(),
        ]);
    }

    public function fileData()
    {
        return $this->data()->merge([
            'id' => (string) $this->id(),
            'user' => $this->user()?->id(),
        ])->all();
    }

    public function fresh()
    {
        return Facades\Passkey::find($this->id);
    }

    public function save()
    {
        Facades\Passkey::save($this);

        return $this;
    }

    public function delete()
    {
        Facades\Passkey::delete($this);

        return true;
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
