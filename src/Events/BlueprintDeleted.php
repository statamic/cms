<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class BlueprintDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $blueprint)
    {
    }

    public function commitMessage()
    {
        return __('Blueprint deleted', [], config('statamic.git.locale'));
    }
}
