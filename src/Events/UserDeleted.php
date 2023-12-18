<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class UserDeleted extends Event implements ProvidesCommitMessage
{
    public $user;
    public $currentUser;

    public function __construct($user, $currentUser = null)
    {
        $this->user = $user;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('User deleted', [], config('statamic.git.locale'));
    }
}
