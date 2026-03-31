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
 * @method static bool isRepo()
 * @method static string currentSha()
 * @method static string|null getStacheRef()
 * @method static void setStacheRef(string $sha)
 * @method static \Illuminate\Support\Collection parseDiffOutput(?string $output)
 * @method static \Illuminate\Support\Collection|null stacheDiff(bool $includeDirty = false)
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
