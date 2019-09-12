<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Data\DataRepository;

class Data extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DataRepository::class;
    }
}
