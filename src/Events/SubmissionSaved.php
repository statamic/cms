<?php

namespace Statamic\Events;

class SubmissionSaved extends Saved
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Submission saved');
    }
}
