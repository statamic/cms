<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Forms\FormRepository;

class Form extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FormRepository::class;
    }
}
