<?php

namespace Statamic\Facades\CP;

use Illuminate\Support\Facades\Facade;
use Statamic\CP\Toasts\Manager;

/**
 * @method static void push(\Statamic\CP\Toasts\Toast $toast)
 * @method static \Statamic\CP\Toasts\Toast[] all()
 *
 * @see \Statamic\CP\Toasts\Manager
 */
class Toast extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Manager::class;
    }
}
