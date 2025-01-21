<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Query\Scopes\ScopeRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static mixed find(string $key, array $context = [])
 * @method static mixed filters(string $key, array $context = [])
 * @method static ScopeRepository remove(string $handle)
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
