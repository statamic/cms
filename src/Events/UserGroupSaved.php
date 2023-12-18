<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class UserGroupSaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

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
