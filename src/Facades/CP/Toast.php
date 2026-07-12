<?php

namespace Statamic\Facades\CP;

use Illuminate\Support\Facades\Facade;
use Statamic\CP\Toasts\Manager;

/**
 * @method static void push(\Statamic\CP\Toasts\Toast $toast)
 * @method static \Statamic\CP\Toasts\Toast[] all()
 * @method static \Illuminate\Support\Collection collect()
 * @method static array toArray()
 * @method static \Statamic\CP\Toasts\Toast info(string $message)
 * @method static \Statamic\CP\Toasts\Toast error(string $message)
 * @method static \Statamic\CP\Toasts\Toast success(string $message)
 * @method static void clear()
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
