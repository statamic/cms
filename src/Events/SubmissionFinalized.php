<?php

namespace Statamic\Events;

class SubmissionFinalized extends Event
{
    public function __construct(public $submission)
    {
    }
}
