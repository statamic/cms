<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class NavDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $nav)
    {
    }

    public function commitMessage()
    {
        return __('Navigation deleted', [], config('statamic.git.locale'));
    }
}
