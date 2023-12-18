<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class CollectionTreeSaved extends Event implements ProvidesCommitMessage
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
        return __('Collection tree saved', [], config('statamic.git.locale'));
    }
}
