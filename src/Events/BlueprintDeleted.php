<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class BlueprintDeleted extends Event implements ProvidesCommitMessage
{
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
