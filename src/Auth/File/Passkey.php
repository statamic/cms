<?php

namespace Statamic\Auth\File;

use Statamic\Auth\Passkey as BasePasskey;

class Passkey extends BasePasskey
{
    public function delete(): bool
    {
        $user = $this->user();

        $user->passkeys($user->passkeys()->reject(fn ($key) => $key->id() == $this->id()));

        $user->save();

        return true;
    }

    public function save(): bool
    {
        $user = $this->user();

        $passkeys = $user->passkeys()->reject(fn ($key) => $key->id() == $this->id());

        $user->passkeys($passkeys->push($this));

        $user->save();

        return true;
    }

    public function fileData()
    {
        return [
            'name' => $this->name(),
            'last_login' => $this->lastLogin()?->timestamp ?? null,
            'credential' => $this->credential()->jsonSerialize(),
        ];
    }
}
