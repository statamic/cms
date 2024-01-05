<?php

namespace Statamic\Events;

use Statamic\Contracts\Git\ProvidesCommitMessage;

class SubmissionDeleted extends Event implements ProvidesCommitMessage
{
    public $submission;

    public function __construct($submission)
    {
        $this->submission = $submission;
    }

    public function commitMessage()
    {
        return __('Submission deleted', [], config('statamic.git.locale'));
    }
}
