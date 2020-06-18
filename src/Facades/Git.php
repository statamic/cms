<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Statamic\Git\Git statuses($handle)
 * @method static \Statamic\Git\Git commit($message = null)
 * @method static \Statamic\Git\Git push($message = null)
 * @method static \Statamic\Git\Git gitUserName()
 * @method static \Statamic\Git\Git gitUserEmail()
 *
 * @see \Statamic\Git\Git
 */
class Git extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Git\Git::class;
    }
}
