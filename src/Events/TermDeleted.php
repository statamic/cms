<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class TermDeleted extends Event implements ProvidesCommitMessage
{
    public function __construct(public $term)
    {
    }

    public function commitMessage()
    {
        return __('Term deleted', [], config('statamic.git.locale'));
    }
}
