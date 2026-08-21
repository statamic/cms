<?php

namespace Statamic\Contracts\Tokens;

use Illuminate\Support\Collection;

interface TokenRepository
{
    public function make(?string $token, string $handler, array $data = []): Token;

    public function find(string $token): ?Token;

    public function all(): Collection;

    public function save(Token $token): bool;

    public function delete(Token $token): bool;

    public function collectGarbage(): void;
}
