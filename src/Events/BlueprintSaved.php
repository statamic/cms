<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class BlueprintSaved extends Event implements ProvidesCommitMessage
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
        return __('Blueprint saved', [], config('statamic.git.locale'));
    }
}
