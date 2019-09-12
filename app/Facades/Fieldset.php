<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Fields\FieldsetRepository;

class Fieldset extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FieldsetRepository::class;
    }
}
