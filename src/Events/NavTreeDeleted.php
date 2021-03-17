<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class NavTreeDeleted extends Event implements ProvidesCommitMessage
{
    public $tree;

    public function __construct($tree)
    {
        $this->tree = $tree;
    }

    public function commitMessage()
    {
        return __('Navigation tree deleted', [], config('statamic.git.locale'));
    }
}
