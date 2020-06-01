<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Data\DataRepository;

/**
 * @method static find($reference)
 * @method static findByUri($uri, $site = null)
 * @method static splitReference($reference)
 *
 * @see \Statamic\Data\DataRepository
 */
class Data extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DataRepository::class;
    }
}
