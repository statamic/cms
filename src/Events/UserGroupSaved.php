<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class UserGroupSaved extends Event implements ProvidesCommitMessage
{
    public $group;

    public function __construct($group)
    {
        $this->group = $group;
    }

    public function commitMessage()
    {
        return __('User group saved', [], config('statamic.git.locale'));
    }
}
