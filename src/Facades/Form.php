<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Forms\FormRepository;

/**
 * @method static \Statamic\Contracts\Forms\Form find($handle)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Statamic\Contracts\Forms\Form make($handle = null)
 *
 * @see \Statamic\Contracts\Forms\FormRepository
 */
class Form extends Facade
{
    protected static function getFacadeAccessor()
    {
        return FormRepository::class;
    }
}
