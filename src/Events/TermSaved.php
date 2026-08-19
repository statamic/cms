<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

use function Statamic\trans as __;

class TermSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $term)
    {
    }

    public function commitMessage()
    {
        return __('Term saved', [], config('statamic.git.locale'));
    }
}
