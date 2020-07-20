<?php

namespace Statamic\Events;

class SubmissionSaved extends Saved
{
    public $submission;

    public function __construct($submission)
    {
        $this->submission = $submission;
    }

    public function commitMessage()
    {
        return __('Submission saved');
    }
}
