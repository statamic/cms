<?php

namespace Statamic\Contracts\Tokens;

interface TokenRepository
{
    public function make(?string $token, string $handler, array $data = []): Token;

    public function find(string $token): ?Token;

    public function save(Token $token): bool;

    public function delete(Token $token): bool;

    public function collectGarbage(): void;
}
