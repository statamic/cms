<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class CollectionSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $collection)
    {
    }

    public function commitMessage()
    {
        return __('Collection saved', [], config('statamic.git.locale'));
    }
}
