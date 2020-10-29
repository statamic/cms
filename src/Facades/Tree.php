<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Structures\TreeRepository;

class Tree extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TreeRepository::class;
    }
}
