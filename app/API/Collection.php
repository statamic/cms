<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Data\Repositories\CollectionRepository;

class Collection extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CollectionRepository::class;
    }
}
