<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class UserGroupSaved extends Event implements ProvidesCommitMessage
{
    public $group;
    public $currentUser;

    public function __construct($group, $currentUser = null)
    {
        $this->group = $group;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('User group saved', [], config('statamic.git.locale'));
    }
}
