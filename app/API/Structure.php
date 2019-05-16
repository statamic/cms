<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Data\Repositories\StructureRepository;

class Structure extends Facade
{
    protected static function getFacadeAccessor()
    {
        return StructureRepository::class;
    }
}
