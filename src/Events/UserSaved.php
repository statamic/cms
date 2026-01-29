<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class UserSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $user)
    {
    }

    public function commitMessage()
    {
        return __('User saved', [], config('statamic.git.locale'));
    }
}
