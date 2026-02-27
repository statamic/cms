<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class SubmissionSaved extends Event implements ProvidesCommitMessage
{
    public function __construct(public $submission)
    {
    }

    public function commitMessage()
    {
        return __('Submission saved', [], config('statamic.git.locale'));
    }
}
