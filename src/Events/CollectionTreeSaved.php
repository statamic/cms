<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class CollectionTreeSaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public $tree;

    public function __construct($tree)
    {
        $this->tree = $tree;
    }

    public function commitMessage()
    {
        return __('Collection tree saved', [], config('statamic.git.locale'));
    }
}
