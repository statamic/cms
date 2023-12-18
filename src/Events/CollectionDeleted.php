<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class CollectionDeleted extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

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
