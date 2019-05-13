<?php

namespace Statamic\API;

use Statamic\Data\DataRepository;
use Illuminate\Support\Facades\Facade;

class Data extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DataRepository::class;
    }
}
