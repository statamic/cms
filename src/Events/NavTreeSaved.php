<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class NavTreeSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $tree)
    {
    }

    public function commitMessage()
    {
        return __('Navigation tree saved', [], config('statamic.git.locale'));
    }
}
