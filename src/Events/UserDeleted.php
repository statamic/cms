<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

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
