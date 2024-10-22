<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\PasskeyRepository;

/**
 * @method static \lluminate\Support\Collection all()
 * @method static null|\Statamic\Contracts\Auth\Passkey find($id)
 * @method static \Statamic\Contracts\Auth\Passkey make()
 *
 * @see \Statamic\Auth\File\PasskeyRepository
 */
class Passkey extends Facade
{
    protected static function getFacadeAccessor()
    {
        return PasskeyRepository::class;
    }
}
