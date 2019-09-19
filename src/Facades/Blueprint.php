<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Fields\BlueprintRepository;

class Blueprint extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BlueprintRepository::class;
    }
}
