<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class CollectionTreeSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $tree)
    {
    }

    public function commitMessage()
    {
        return __('Collection tree saved', [], config('statamic.git.locale'));
    }
}
