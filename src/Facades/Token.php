<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Tokens\TokenRepository;

/**
 * @method static \Statamic\Contracts\Tokens\Token make(?string $token, string $handler, array $data = [])
 * @method static \Statamic\Contracts\Tokens\Token find(string $token)
 * @method static bool save(\Statamic\Contracts\Tokens\Token $token)
 * @method static bool delete(\Statamic\Contracts\Tokens\Token $token)
 * @method static void collectGarbage()
 */
class Token extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TokenRepository::class;
    }
}
