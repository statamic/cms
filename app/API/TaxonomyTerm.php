<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class TaxonomyTerm extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\TaxonomyTerm::class;
    }
}
