<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class GlobalSetSaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public $globals;

    public function __construct($globals)
    {
        $this->globals = $globals;
    }

    public function commitMessage()
    {
        return __('Global Set saved', [], config('statamic.git.locale'));
    }
}
