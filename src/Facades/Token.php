<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Tokens\TokenRepository;

class Token extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TokenRepository::class;
    }
}
