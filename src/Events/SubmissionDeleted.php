<?php

namespace Statamic\Events;

class SubmissionDeleted extends Deleted
{
    public $submission;

    public function __construct($submission)
    {
        $this->submission = $submission;
    }

    public function commitMessage()
    {
        return __('Submission deleted');
    }
}
