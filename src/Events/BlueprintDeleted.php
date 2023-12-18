<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class BlueprintDeleted extends Event implements ProvidesCommitMessage
{
    public $blueprint;
    public $currentUser;

    public function __construct($blueprint, $currentUser = null)
    {
        $this->blueprint = $blueprint;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Blueprint deleted', [], config('statamic.git.locale'));
    }
}
