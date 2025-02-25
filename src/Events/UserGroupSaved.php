<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class UserGroupSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $group)
    {
    }

    public function commitMessage()
    {
        return __('User group saved', [], config('statamic.git.locale'));
    }
}
