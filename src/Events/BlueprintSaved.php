<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class BlueprintSaved extends Event implements ProvidesCommitMessage
{
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
