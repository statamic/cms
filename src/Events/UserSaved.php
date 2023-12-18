<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class UserSaved extends Event implements ProvidesCommitMessage
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function commitMessage()
    {
        return __('User saved', [], config('statamic.git.locale'));
    }
}
