<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Forms\Connectors\ConnectorRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static \Statamic\Forms\Connectors\Connector|null find(string $handle)
 * @method static void routes()
 *
 * @see \Statamic\Forms\Connectors\ConnectorRepository
 */
class FormConnector extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ConnectorRepository::class;
    }
}
