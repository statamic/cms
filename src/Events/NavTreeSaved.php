<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class NavTreeSaved extends Event implements ProvidesCommitMessage
{
    public $tree;

    public function __construct($tree)
    {
        $this->tree = $tree;
    }

    public function commitMessage()
    {
        return __('Navigation tree saved', [], config('statamic.git.locale'));
    }
}
