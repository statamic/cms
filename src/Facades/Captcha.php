<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Forms\Captcha\CaptchaManager;

/**
 * @method static \Statamic\Contracts\Forms\Captcha driver(?string $name = null)
 * @method static \Statamic\Forms\Captcha\CaptchaManager extend(string $driver, \Closure $callback)
 * @method static bool enabled()
 * @method static \Statamic\Contracts\Forms\Captcha|null forForm(\Statamic\Contracts\Forms\Form $form)
 * @method static string getDefaultDriver()
 *
 * @see CaptchaManager
 */
class Captcha extends Facade
{
    protected static function getFacadeAccessor()
    {
        return CaptchaManager::class;
    }
}
