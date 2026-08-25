<?php

namespace Statamic\Tokens;

use Illuminate\Support\Collection;
use Statamic\Contracts\Tokens\Token as TokenContract;
use Statamic\Contracts\Tokens\TokenRepository as Contract;

abstract class TokenRepository implements Contract
{
    public function make(?string $token, string $handler, array $data = []): TokenContract
    {
        return app()->makeWith(TokenContract::class, compact('token', 'handler', 'data'));
    }

    public function all(): Collection
    {
        return collect();
    }

    abstract public static function bindings(): array;
}
