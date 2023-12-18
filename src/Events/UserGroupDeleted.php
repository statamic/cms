<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class UserGroupDeleted extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public $group;

    public function __construct($group)
    {
        $this->group = $group;
    }

    public function commitMessage()
    {
        return __('User group deleted', [], config('statamic.git.locale'));
    }
}
