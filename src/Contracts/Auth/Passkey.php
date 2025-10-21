<?php

namespace Statamic\Contracts\Auth;

use Carbon\Carbon;
use Webauthn\PublicKeyCredentialSource;

interface Passkey
{
    public function id(): string;

    public function user(): ?User;

    public function setUser(string|User $user): self;

    public function credential(): PublicKeyCredentialSource;

    public function setCredential(array|PublicKeyCredentialSource $credential): self;

    public function lastLogin(): ?Carbon;

    public function setLastLogin(null|int|Carbon $time): self;

    public function save(): bool;

    public function delete(): bool;
}
