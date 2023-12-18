<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class NavTreeDeleted extends Event implements ProvidesCommitMessage
{
    public $tree;
    public $currentUser;

    public function __construct($tree, $currentUser = null)
    {
        $this->tree = $tree;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Navigation tree deleted', [], config('statamic.git.locale'));
    }
}
