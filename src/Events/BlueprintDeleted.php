<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class BlueprintDeleted extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

    public $blueprint;

    public function __construct($blueprint)
    {
        $this->blueprint = $blueprint;
    }

    public function commitMessage()
    {
        return __('Blueprint deleted', [], config('statamic.git.locale'));
    }
}
