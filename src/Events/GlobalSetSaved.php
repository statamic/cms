<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class GlobalSetSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $globals)
    {
    }

    public function commitMessage()
    {
        return __('Global Set saved', [], config('statamic.git.locale'));
    }
}
