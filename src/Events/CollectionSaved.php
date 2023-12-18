<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class CollectionSaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function commitMessage()
    {
        return __('Collection saved', [], config('statamic.git.locale'));
    }
}
