<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class SubmissionSaved extends Event implements ProvidesCommitMessage
{
    public $submission;

    public function __construct($submission)
    {
        $this->submission = $submission;
    }

    public function commitMessage()
    {
        return __('Submission saved', [], config('statamic.git.locale'));
    }
}
