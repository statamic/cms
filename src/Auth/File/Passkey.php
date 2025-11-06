<?php

namespace Statamic\Auth\File;

use Statamic\Auth\WebAuthn\Passkey as BasePasskey;
use Statamic\Auth\WebAuthn\Serializer;

class Passkey extends BasePasskey
{
    public function delete(): bool
    {
        /** @var User $user */
        $user = $this->user();

        $user->setPasskeys($user->passkeys()->except($this->id()));

        $user->save();

        return true;
    }

    public function save(): bool
    {
        /** @var User $user */
        $user = $this->user();

        $passkeys = $user->passkeys()->except($this->id())->push($this);

        $user->setPasskeys($passkeys);

        $user->save();

        return true;
    }

    public function fileData()
    {
        return [
            'name' => $this->name(),
            'last_login' => $this->lastLogin()?->timestamp ?? null,
            'credential' => app(Serializer::class)->normalize($this->credential()),
        ];
    }
}
