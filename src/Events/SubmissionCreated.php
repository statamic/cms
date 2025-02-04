<?php

namespace Statamic\Events;

class SubmissionCreated extends Event
{
    public function __construct(public $submission)
    {
    }
}
