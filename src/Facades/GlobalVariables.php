<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Globals\GlobalVariablesRepository;

/**
 * @method static \Statamic\Globals\VariablesCollection all()
 * @method static null|\Statamic\Globals\Variables find($id)
 * @method static \Statamic\Globals\VariablesCollection whereSet($set)
 * @method static void save($variable);
 *
 * @see \Statamic\Globals\VariablesCollection
 */
class GlobalVariables extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GlobalVariablesRepository::class;
    }
}
