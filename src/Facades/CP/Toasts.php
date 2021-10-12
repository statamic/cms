<?php

namespace Statamic\Facades\CP;

use Illuminate\Support\Facades\Facade;
use Statamic\CP\Toasts\ToastsHolder;

/**
 * @method static void push(\Statamic\CP\Toasts\Toast $toast)
 * @method static \Statamic\CP\Toasts\Toast[] all()
 *
 * @see \Statamic\CP\Toasts\ToastsHolder
 */
class Toasts extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ToastsHolder::class;
    }
}
