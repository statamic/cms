<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class YAML extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Yaml\Yaml::class;
    }
}
