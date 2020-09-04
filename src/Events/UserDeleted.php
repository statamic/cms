<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class UserDeleted extends Event implements ProvidesCommitMessage
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function commitMessage()
    {
        return __('User deleted', [], config('statamic.git.locale'));
    }
}
