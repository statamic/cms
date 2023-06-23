<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Globals\GlobalVariableRepository;

/**
 * @method static \Statamic\Globals\VariableCollection all()
 * @method static null|\Statamic\Globals\Variables find($id)
 * @method static null|\Statamic\Globals\Variables findBySet($set)
 * @method static void save($variable);
 *
 * @see \Statamic\Globals\VariableCollection
 */
class GlobalSetVariable extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GlobalVariableRepository::class;
    }
}
