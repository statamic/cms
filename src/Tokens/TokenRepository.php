<?php

namespace Statamic\Tokens;

use Statamic\Contracts\Tokens\Token as TokenContract;
use Statamic\Contracts\Tokens\TokenRepository as Contract;

abstract class TokenRepository implements Contract
{
    public function make(?string $token, string $handler, array $data = []): TokenContract
    {
        return app()->makeWith(TokenContract::class, compact('token', 'handler', 'data'));
    }

    abstract public static function bindings(): array;
}
