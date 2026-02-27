<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class GlobalSetDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $globals)
    {
    }

    public function commitMessage()
    {
        return __('Global Set deleted', [], config('statamic.git.locale'));
    }
}
