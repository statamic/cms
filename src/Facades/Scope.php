<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Query\Scopes\ScopeRepository;

class Scope extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ScopeRepository::class;
    }
}
