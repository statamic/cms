<?php

namespace Statamic\Events;

class SubmissionDeleted extends Deleted
{
    public $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function commitMessage()
    {
        return __('Submission deleted');
    }
}
