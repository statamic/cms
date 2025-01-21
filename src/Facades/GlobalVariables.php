<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Globals\GlobalVariablesRepository;

/**
 * @method static \Statamic\Globals\VariablesCollection all()
 * @method static null|\Statamic\Globals\Variables find($id)
 * @method static \Statamic\Globals\Variables findOrFail($id)
 * @method static \Statamic\Globals\VariablesCollection whereSet(string $handle)
 * @method static void save(\Statamic\Globals\Variables $variable)
 * @method static void delete(\Statamic\Globals\Variables $variable)
 *
 * @see \Statamic\Stache\Repositories\GlobalVariablesRepository
 * @see \Statamic\Globals\Variables
 */
class GlobalVariables extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GlobalVariablesRepository::class;
    }
}
