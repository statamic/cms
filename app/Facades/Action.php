<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Actions\ActionRepository;

class Action extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ActionRepository::class;
    }
}
