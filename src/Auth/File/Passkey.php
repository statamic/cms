<?php

namespace Statamic\Auth\File;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Statamic\Auth\WebAuthn\Passkey as BasePasskey;
use Statamic\Auth\WebAuthn\Serializer;

class Passkey extends BasePasskey
{
    public function delete(): bool
    {
        /** @var User $user */
        $user = $this->user();

        $remaining = $user->passkeys()->except($this->id());

        $user->setPasskeys($remaining);

        $this->setLastLogins($user, $remaining);

        $user->save();

        return true;
    }

    public function save(): bool
    {
        /** @var User $user */
        $user = $this->user();

        $passkeys = $user->passkeys()->except($this->id())->push($this);

        $user->setPasskeys($passkeys);

        $this->setLastLogins($user, $passkeys);

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
            $this->setLastLogin(
                $this->user()->getMeta('passkey_last_logins', [])[$this->id()] ?? null
            );
        }

        return parent::lastLogin();
    }

    private function setLastLogins(User $user, Collection $passkeys): void
    {
        $timestamps = $passkeys
            ->mapWithKeys(fn (Passkey $passkey) => [$passkey->id() => $passkey->lastLogin()?->timestamp])
            ->filter()
            ->all();

        $user->setMeta('passkey_last_logins', $timestamps);
    }
}
