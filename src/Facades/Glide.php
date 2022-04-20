<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Imaging\GlideManager;

class Glide extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GlideManager::class;
    }
}
