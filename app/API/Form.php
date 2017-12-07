<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Form extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Form::class;
    }
}
