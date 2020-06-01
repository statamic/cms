<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Query\Scopes\ScopeRepository;

/**
 * @method static mixed all()
 * @method static mixed find($key, $context = [])
 * @method static mixed filters($key, $context = [])
 *
 * @see \Statamic\Query\Scopes\ScopeRepository
 */
class Scope extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ScopeRepository::class;
    }
}
