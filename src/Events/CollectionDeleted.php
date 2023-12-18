<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class CollectionDeleted extends Event implements ProvidesCommitMessage
{
    public $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function commitMessage()
    {
        return __('Collection deleted', [], config('statamic.git.locale'));
    }
}
