<?php

namespace Statamic\Auth;

use Carbon\Carbon;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Statamic\Auth\WebAuthn\Serializer;
use Statamic\Contracts\Auth\Passkey as Contract;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\User;
use Webauthn\PublicKeyCredentialSource;

abstract class Passkey implements Contract
{
    private string $name;
    private string $user;
    private PublicKeyCredentialSource $credential;
    private ?Carbon $lastLogin = null;

    public function id(): string
    {
        return Base64UrlSafe::encodeUnpadded($this->credential()->publicKeyCredentialId);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setUser(string|UserContract $user): self
    {
        $this->user = $user instanceof UserContract ? $user->id() : $user;

        return $this;
    }

    public function user(): ?UserContract
    {
        return User::find($this->user);
    }

    public function credential(): PublicKeyCredentialSource
    {
        return $this->credential;
    }

    public function setCredential(array|PublicKeyCredentialSource $credential): Contract
    {
        $this->credential = $credential instanceof PublicKeyCredentialSource
            ? $credential
            : $this->credentialFromArray($credential);

        return $this;
    }

    public function lastLogin(): ?Carbon
    {
        return $this->lastLogin;
    }

    public function setLastLogin(null|int|Carbon $time): Contract
    {
        if ($time === null) {
            $this->lastLogin = null;

            return $this;
        }

        $this->lastLogin = $time instanceof Carbon ? $time : Carbon::createFromTimestamp($time);

        return $this;
    }

    public function __serialize(): array
    {
        return [
            'name' => $this->name,
            'user' => $this->user,
            'credential' => $this->credentialToArray($this->credential),
            'last_login' => $this->lastLogin?->timestamp,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->name = $data['name'];
        $this->user = $data['user'];
        $this->credential = $this->credentialFromArray($data['credential']);
        $this->lastLogin = $data['last_login'] ? Carbon::createFromTimestamp($data['last_login']) : null;
    }

    private function credentialToArray(PublicKeyCredentialSource $credential): array
    {
        $json = app(Serializer::class)->serialize($credential, 'json');

        return json_decode($json, true);
    }

    private function credentialFromArray(array $array): PublicKeyCredentialSource
    {
        return app(Serializer::class)->deserialize(json_encode($array), PublicKeyCredentialSource::class, 'json');
    }
}
