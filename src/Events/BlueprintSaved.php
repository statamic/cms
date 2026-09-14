<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class BlueprintSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $blueprint)
    {
    }

    public function commitMessage()
    {
        return __('Blueprint saved', [], config('statamic.git.locale'));
    }
}
