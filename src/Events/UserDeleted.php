<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class UserDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $user)
    {
    }

    public function commitMessage()
    {
        return __('User deleted', [], config('statamic.git.locale'));
    }
}
