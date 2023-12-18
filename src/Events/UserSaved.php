<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class UserSaved extends Event implements ProvidesCommitMessage
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
        return __('User saved', [], config('statamic.git.locale'));
    }
}
