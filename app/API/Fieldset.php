<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Fieldset extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Fieldset::class;
    }
}
