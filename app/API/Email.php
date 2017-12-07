<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class Email extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Email::class;
    }
}
