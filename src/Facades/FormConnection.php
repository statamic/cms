<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Forms\Connections\ConnectionRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \Statamic\Forms\Connections\Connection|null find(string $handle)
 * @method static void routes()
 * @method static array classes()
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
