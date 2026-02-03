<?php

namespace Statamic\Auth\File;

use Carbon\Carbon;
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

        $passkeys->each(function ($passkey) use ($user) {
            if ($lastLogin = $passkey->lastLogin()) {
                $user->setMeta('passkey_'.$passkey->id().'_last_login', $lastLogin);
            }
        });

        $user->save();

        return true;
    }

    public function fileData()
    {
        return [
            'name' => $this->name(),
            'credential' => app(Serializer::class)->normalize($this->credential()),
        ];
    }

    public function lastLogin(): ?Carbon
    {
        if (! parent::lastLogin()) {
            $this->setLastLogin($this->user()->getMeta('passkey_'.$this->id().'_last_login'));
        }

        return parent::lastLogin();
    }
}
