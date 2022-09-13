<?php

namespace Statamic\Events;

class SubmissionSaving extends Event
{
    public $submission;

    public function __construct($submission)
    {
        $this->submission = $submission;
    }

    /**
     * Dispatch the event with the given arguments, and halt on first non-null listener response.
     *
     * @return mixed
     */
    public static function dispatch()
    {
        return event(new static(...func_get_args()), [], true);
    }
}
