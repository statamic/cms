<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Data\Repositories\EntryRepository;

class Entry extends Facade
{
    protected static function getFacadeAccessor()
    {
        return EntryRepository::class;
    }
}
