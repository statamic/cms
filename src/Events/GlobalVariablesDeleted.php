<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class GlobalVariablesDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $variables)
    {
    }

    public function commitMessage()
    {
        return __('Global variables deleted', [], config('statamic.git.locale'));
    }
}
