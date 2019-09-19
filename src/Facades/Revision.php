<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Revisions\RevisionRepository;

class Revision extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RevisionRepository::class;
    }
}
