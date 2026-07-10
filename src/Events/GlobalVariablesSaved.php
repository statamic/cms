<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class GlobalVariablesSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $variables)
    {
    }

    public function commitMessage()
    {
        return __('Global variables saved', [], config('statamic.git.locale'));
    }
}
