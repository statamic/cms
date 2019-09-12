<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Data\Repositories\TermRepository;

class Term extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TermRepository::class;
    }
}
