<?php

namespace Statamic\Contracts\Auth;

use Carbon\Carbon;
use Webauthn\CredentialRecord;

interface Passkey
{
    public function id(): string;

    public function name(): string;

    public function setName(string $name): self;

    public function user(): ?User;

    public function setUser(string|User $user): self;

    public function credential(): CredentialRecord;

    public function setCredential(array|CredentialRecord $credential): self;

    public function lastLogin(): ?Carbon;

    public function setLastLogin(null|int|Carbon $time): self;

    public function save(): bool;

    public function delete(): bool;
}
