<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\User as UserContract;

/**
 * @method static void listen(string $event)
 * @method static \Illuminate\Support\Collection|null statuses()
 * @method static \Statamic\Git\Git as(?UserContract $user)
 * @method static \Illuminate\Support\Collection commit(string $message = null)
 * @method static void dispatchCommit(string $message = null)
 * @method static string gitUserName()
 * @method static string gitUserEmail()
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
