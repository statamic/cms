<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class BlueprintReset extends Event implements ProvidesCommitMessage
{
    public $blueprint;

    public function __construct($blueprint)
    {
        $this->blueprint = $blueprint;
    }

    public function commitMessage()
    {
        return __('Blueprint reset', [], config('statamic.git.locale'));
    }
}
