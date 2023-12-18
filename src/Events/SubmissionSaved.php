<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class SubmissionSaved extends Event implements ProvidesCommitMessage
{
    public $submission;
    public $currentUser;

    public function __construct($submission, $currentUser = null)
    {
        $this->submission = $submission;
        $this->currentUser = $currentUser;
    }

    public function commitMessage()
    {
        return __('Submission saved', [], config('statamic.git.locale'));
    }
}
