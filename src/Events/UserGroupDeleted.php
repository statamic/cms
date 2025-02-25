<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class UserGroupDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $group)
    {
    }

    public function commitMessage()
    {
        return __('User group deleted', [], config('statamic.git.locale'));
    }
}
