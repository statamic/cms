<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class BlueprintSaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public $blueprint;

    public function __construct($blueprint)
    {
        $this->blueprint = $blueprint;
    }

    public function commitMessage()
    {
        return __('Blueprint saved', [], config('statamic.git.locale'));
    }
}
