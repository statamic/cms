<?php

namespace Statamic\Facades;

use Statamic\Forms\FormRepository;
use Illuminate\Support\Facades\Facade;

class Form extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FormRepository::class;
    }
}
