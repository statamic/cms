<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class GlobalSetDeleted extends Event implements ProvidesCommitMessage
{
    public $globals;

    public function __construct($globals)
    {
        $this->globals = $globals;
    }

    public function commitMessage()
    {
        return __('Global Set deleted', [], config('statamic.git.locale'));
    }
}
