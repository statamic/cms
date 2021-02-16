<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class CollectionStructureTreeDeleted extends Event implements ProvidesCommitMessage
{
    public $tree;

    public function __construct($tree)
    {
        $this->tree = $tree;
    }

    public function commitMessage()
    {
        return __('Collection structure tree deleted', [], config('statamic.git.locale'));
    }
}
