<?php

namespace Statamic\Events;

use Statamic\Contracts\Forms\Submission;

class FormSubmitted extends Event
{
    public $submission;

    public function __construct(Submission $submission)
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
