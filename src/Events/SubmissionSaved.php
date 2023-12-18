<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Concerns\TracksAuthenticatedUser;

class SubmissionSaved extends Event implements ProvidesCommitMessage
{
    use TracksAuthenticatedUser;

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
