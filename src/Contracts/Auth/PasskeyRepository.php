<?php

namespace Statamic\Contracts\Auth;

use Illuminate\Support\Collection;

interface PasskeyRepository
{
    public function all(): Collection;

    public function find(string $id): ?Passkey;

    public function make(): Passkey;

    public function save(Passkey $passkey);

    public function delete(Passkey $passkey);
}
