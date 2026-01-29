<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class TermReferencesUpdated extends Event implements ProvidesCommitMessage
{
    public function __construct(public $term)
    {
    }

    public function commitMessage()
    {
        return __('Term references updated', [], config('statamic.git.locale'));
    }
}
