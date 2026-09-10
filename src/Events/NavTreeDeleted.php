<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class NavTreeDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $tree)
    {
    }

    public function commitMessage()
    {
        return __('Navigation tree deleted', [], config('statamic.git.locale'));
    }
}
