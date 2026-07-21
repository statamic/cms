<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Forms\Connections\ConnectionRepository;

/**
 * @method static self boot()
 * @method static void extend(\Closure $callback)
 * @method static \Statamic\Forms\Connections\Connection make(string $handle)
 * @method static \Statamic\Forms\Connections\Connection register($connection)
 * @method static \Illuminate\Support\Collection all()
 * @method static mixed find(string $handle)
 * @method static void routes()
 *
 * @see \Statamic\Forms\Connections\ConnectionRepository
 */
class FormConnection extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ConnectionRepository::class;
    }
}
