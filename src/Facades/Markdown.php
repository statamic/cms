<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Markdown\Manager;

class Markdown extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
