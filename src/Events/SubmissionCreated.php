<?php

namespace Statamic\Events;

class SubmissionCreated extends Event
{
    public $submission;

    public function __construct($submission)
    {
        $this->submission = $submission;
    }
}
