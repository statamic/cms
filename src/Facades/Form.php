<?php

namespace Statamic\Facades;

use Statamic\Forms\FormRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Statamic\Contracts\Forms\Form find($handle)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Statamic\Contracts\Forms\Form make($handle = null)
 *
 * @see \Statamic\Forms\FormRepository
 */
class Form extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FormRepository::class;
    }
}
