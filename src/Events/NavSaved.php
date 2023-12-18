<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class NavSaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public $nav;

    public function __construct($nav)
    {
        $this->nav = $nav;
    }

    public function commitMessage()
    {
        return __('Navigation saved', [], config('statamic.git.locale'));
    }
}
