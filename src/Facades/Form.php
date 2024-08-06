<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Forms\FormRepository;

/**
 * @method static \Statamic\Contracts\Forms\Form find($handle)
 * @method static \Statamic\Contracts\Forms\Form findOrFail($handle)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Statamic\Contracts\Forms\Form make($handle = null)
 * @method static array extraConfigFor($handle)
 * @method static void appendConfigFields($handle, $display, $fields)
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
