<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class UserSaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

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
