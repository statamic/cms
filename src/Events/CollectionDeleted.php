<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class CollectionDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $collection)
    {
    }

    public function commitMessage()
    {
        return __('Collection deleted', [], config('statamic.git.locale'));
    }
}
