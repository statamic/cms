<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;

class TaxonomyTerms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\TaxonomyTerms::class;
    }
}
