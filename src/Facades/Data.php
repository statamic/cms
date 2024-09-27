<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Data\DataRepository;

/**
 * @method static self setRepository(string $handle, string $value)
 * @method static mixed find(string $reference)
 * @method static mixed findByUri(string $uri, $site = null)
 * @method static mixed findByRequestUrl(string $url)
 * @method static array splitReference(string $reference)
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
